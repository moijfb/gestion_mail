<?php

require_once BASE_PATH . '/src/controllers/BaseController.php';

class SettingsController extends BaseController
{
    private $settings;
    private $fileManager;

    public function __construct()
    {
        parent::__construct();
        $this->settings = new Settings();
        $this->fileManager = new FileManager();
    }

    public function handle($uri, $method)
    {
        if (strpos($uri, '/settings/password') === 0 && $method === 'POST') {
            $this->changePassword();
        } elseif (strpos($uri, '/settings/backup') === 0) {
            $this->createBackup();
        } elseif ($method === 'POST') {
            $this->saveSettings();
        } else {
            $this->showSettings();
        }
    }

    /**
     * Affiche la page des paramètres
     */
    private function showSettings()
    {
        $data = [
            'settings' => $this->settings->getAll(),
            'page_title' => 'Paramètres'
        ];

        $this->renderWithLayout('settings/index', $data);
    }

    /**
     * Sauvegarde les paramètres
     */
    private function saveSettings()
    {
        $this->verifyCsrf();
        $postData = $this->getPostData();

        try {
            // Filtrer et valider les paramètres
            $allowedSettings = [
                'max_per_send',
                'separator',
                'language',
                'timezone',
                'import_encoding',
                'import_separator',
                'auto_backup',
                'backup_frequency',
                'auto_delete_days',
                'pause_between_batches'
            ];

            foreach ($allowedSettings as $key) {
                if (isset($postData[$key])) {
                    $value = $postData[$key];

                    // Validation spécifique
                    if ($key === 'max_per_send') {
                        $value = max(1, intval($value));
                    } elseif ($key === 'auto_backup') {
                        $value = $value === '1' || $value === 'true';
                    } elseif ($key === 'pause_between_batches' || $key === 'auto_delete_days') {
                        $value = max(0, intval($value));
                    }

                    $this->settings->set($key, $value);
                }
            }

            $this->settings->save();

            $this->logger->info('Paramètres mis à jour');
            $this->setFlash('success', 'Paramètres sauvegardés avec succès');

        } catch (Exception $e) {
            $this->logger->error('Erreur sauvegarde paramètres', ['error' => $e->getMessage()]);
            $this->setFlash('error', 'Erreur lors de la sauvegarde: ' . $e->getMessage());
        }

        $this->redirect('/settings');
    }

    /**
     * Change le mot de passe
     */
    private function changePassword()
    {
        $this->verifyCsrf();
        $postData = $this->getPostData();

        $oldPassword = $postData['old_password'] ?? '';
        $newPassword = $postData['new_password'] ?? '';
        $confirmPassword = $postData['confirm_password'] ?? '';

        if (empty($oldPassword) || empty($newPassword) || empty($confirmPassword)) {
            $this->setFlash('error', 'Tous les champs sont obligatoires');
            $this->redirect('/settings');
        }

        if ($newPassword !== $confirmPassword) {
            $this->setFlash('error', 'Les mots de passe ne correspondent pas');
            $this->redirect('/settings');
        }

        if (strlen($newPassword) < 6) {
            $this->setFlash('error', 'Le mot de passe doit contenir au moins 6 caractères');
            $this->redirect('/settings');
        }

        $username = $_SESSION['username'];

        if ($this->user->changePassword($username, $oldPassword, $newPassword)) {
            $this->setFlash('success', 'Mot de passe changé avec succès');
        } else {
            $this->setFlash('error', 'Ancien mot de passe incorrect');
        }

        $this->redirect('/settings');
    }

    /**
     * Crée une sauvegarde
     */
    private function createBackup()
    {
        try {
            $backupFile = $this->fileManager->createSnapshot();

            if ($backupFile) {
                $this->logger->info('Sauvegarde créée', ['file' => basename($backupFile)]);
                $this->setFlash('success', 'Sauvegarde créée avec succès: ' . basename($backupFile));
            } else {
                $this->setFlash('error', 'Erreur lors de la création de la sauvegarde');
            }

        } catch (Exception $e) {
            $this->logger->error('Erreur création sauvegarde', ['error' => $e->getMessage()]);
            $this->setFlash('error', 'Erreur: ' . $e->getMessage());
        }

        $this->redirect('/settings');
    }
}
