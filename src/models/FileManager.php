<?php

class FileManager
{
    private $dataPath;
    private $maxRetries = 5;
    private $retryDelay = 100000; // 100ms en microsecondes

    public function __construct()
    {
        $this->dataPath = DATA_PATH;
    }

    /**
     * Lit un fichier JSON avec verrouillage
     */
    public function readJson($filename, $default = [])
    {
        $filepath = $this->dataPath . '/' . $filename;

        if (!file_exists($filepath)) {
            return $default;
        }

        $attempts = 0;
        while ($attempts < $this->maxRetries) {
            $handle = fopen($filepath, 'r');
            if ($handle === false) {
                throw new Exception("Impossible d'ouvrir le fichier: $filename");
            }

            if (flock($handle, LOCK_SH)) {
                $content = stream_get_contents($handle);
                flock($handle, LOCK_UN);
                fclose($handle);

                if ($content === false || trim($content) === '') {
                    return $default;
                }

                $data = json_decode($content, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new Exception("Erreur de décodage JSON dans $filename: " . json_last_error_msg());
                }

                return $data;
            }

            fclose($handle);
            $attempts++;
            usleep($this->retryDelay);
        }

        throw new Exception("Impossible de verrouiller le fichier $filename après $this->maxRetries tentatives");
    }

    /**
     * Écrit dans un fichier JSON avec verrouillage
     */
    public function writeJson($filename, $data, $createBackup = true)
    {
        $filepath = $this->dataPath . '/' . $filename;

        // Créer une sauvegarde si le fichier existe
        if ($createBackup && file_exists($filepath)) {
            $this->createBackup($filename);
        }

        $attempts = 0;
        while ($attempts < $this->maxRetries) {
            $handle = fopen($filepath, 'c');
            if ($handle === false) {
                throw new Exception("Impossible d'ouvrir le fichier: $filename");
            }

            if (flock($handle, LOCK_EX)) {
                ftruncate($handle, 0);
                rewind($handle);

                $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                if ($json === false) {
                    flock($handle, LOCK_UN);
                    fclose($handle);
                    throw new Exception("Erreur d'encodage JSON: " . json_last_error_msg());
                }

                $result = fwrite($handle, $json);
                fflush($handle);
                flock($handle, LOCK_UN);
                fclose($handle);

                if ($result === false) {
                    throw new Exception("Erreur d'écriture dans le fichier: $filename");
                }

                return true;
            }

            fclose($handle);
            $attempts++;
            usleep($this->retryDelay);
        }

        throw new Exception("Impossible de verrouiller le fichier $filename après $this->maxRetries tentatives");
    }

    /**
     * Crée une sauvegarde du fichier
     */
    private function createBackup($filename)
    {
        $filepath = $this->dataPath . '/' . $filename;
        if (!file_exists($filepath)) {
            return;
        }

        $backupDir = $this->dataPath . '/backups';
        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        $timestamp = date('Y-m-d_H-i-s');
        $backupFile = $backupDir . '/' . pathinfo($filename, PATHINFO_FILENAME) . '_' . $timestamp . '.json';

        copy($filepath, $backupFile);

        // Nettoyer les anciennes sauvegardes (garder seulement les 10 dernières)
        $this->cleanOldBackups($backupDir, pathinfo($filename, PATHINFO_FILENAME));
    }

    /**
     * Nettoie les anciennes sauvegardes
     */
    private function cleanOldBackups($backupDir, $baseName, $keepCount = 10)
    {
        $pattern = $backupDir . '/' . $baseName . '_*.json';
        $files = glob($pattern);

        if (count($files) > $keepCount) {
            usort($files, function($a, $b) {
                return filemtime($a) - filemtime($b);
            });

            $filesToDelete = array_slice($files, 0, count($files) - $keepCount);
            foreach ($filesToDelete as $file) {
                unlink($file);
            }
        }
    }

    /**
     * Append à un fichier log
     */
    public function appendLog($filename, $message)
    {
        $filepath = LOGS_PATH . '/' . $filename;
        $timestamp = date('Y-m-d H:i:s');
        $logEntry = "[$timestamp] $message\n";

        $attempts = 0;
        while ($attempts < $this->maxRetries) {
            $handle = fopen($filepath, 'a');
            if ($handle === false) {
                throw new Exception("Impossible d'ouvrir le fichier log: $filename");
            }

            if (flock($handle, LOCK_EX)) {
                fwrite($handle, $logEntry);
                fflush($handle);
                flock($handle, LOCK_UN);
                fclose($handle);
                return true;
            }

            fclose($handle);
            $attempts++;
            usleep($this->retryDelay);
        }

        return false;
    }

    /**
     * Lit les dernières lignes d'un fichier log
     */
    public function readLogTail($filename, $lines = 100)
    {
        $filepath = LOGS_PATH . '/' . $filename;

        if (!file_exists($filepath)) {
            return [];
        }

        $handle = fopen($filepath, 'r');
        if ($handle === false) {
            return [];
        }

        if (flock($handle, LOCK_SH)) {
            $buffer = [];
            while (($line = fgets($handle)) !== false) {
                $buffer[] = $line;
                if (count($buffer) > $lines) {
                    array_shift($buffer);
                }
            }

            flock($handle, LOCK_UN);
            fclose($handle);
            return $buffer;
        }

        fclose($handle);
        return [];
    }

    /**
     * Crée un snapshot complet des données
     */
    public function createSnapshot()
    {
        $snapshotDir = $this->dataPath . '/snapshots';
        if (!is_dir($snapshotDir)) {
            mkdir($snapshotDir, 0755, true);
        }

        $timestamp = date('Y-m-d_H-i-s');
        $zipFile = $snapshotDir . '/snapshot_' . $timestamp . '.zip';

        $zip = new ZipArchive();
        if ($zip->open($zipFile, ZipArchive::CREATE) === true) {
            $files = [
                'contacts.json',
                'groups.json',
                'unsubscribed.json',
                'settings.json',
                'users.json'
            ];

            foreach ($files as $file) {
                $filepath = $this->dataPath . '/' . $file;
                if (file_exists($filepath)) {
                    $zip->addFile($filepath, $file);
                }
            }

            $zip->close();

            // Nettoyer les anciens snapshots (garder les 5 derniers)
            $this->cleanOldSnapshots($snapshotDir);

            return $zipFile;
        }

        return false;
    }

    /**
     * Nettoie les anciens snapshots
     */
    private function cleanOldSnapshots($snapshotDir, $keepCount = 5)
    {
        $pattern = $snapshotDir . '/snapshot_*.zip';
        $files = glob($pattern);

        if (count($files) > $keepCount) {
            usort($files, function($a, $b) {
                return filemtime($a) - filemtime($b);
            });

            $filesToDelete = array_slice($files, 0, count($files) - $keepCount);
            foreach ($filesToDelete as $file) {
                unlink($file);
            }
        }
    }
}
