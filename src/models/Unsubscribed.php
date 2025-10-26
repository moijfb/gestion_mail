<?php

class Unsubscribed
{
    private $fileManager;
    private $logger;
    private $unsubscribedFile = 'unsubscribed.json';

    public function __construct()
    {
        $this->fileManager = new FileManager();
        $this->logger = new Logger();
    }

    /**
     * Récupère tous les désinscrits
     */
    public function getAll()
    {
        return $this->fileManager->readJson($this->unsubscribedFile, []);
    }

    /**
     * Vérifie si un email est désinscrit
     */
    public function isUnsubscribed($email)
    {
        $unsubscribed = $this->getAll();
        $email = strtolower(trim($email));

        foreach ($unsubscribed as $entry) {
            if (strtolower($entry['email']) === $email) {
                return true;
            }
        }

        return false;
    }

    /**
     * Récupère les informations de désinscription d'un email
     */
    public function getByEmail($email)
    {
        $unsubscribed = $this->getAll();
        $email = strtolower(trim($email));

        foreach ($unsubscribed as $entry) {
            if (strtolower($entry['email']) === $email) {
                return $entry;
            }
        }

        return null;
    }

    /**
     * Ajoute un email à la liste des désinscrits
     */
    public function add($email, $reason = '', $source = 'manual')
    {
        if (empty($email)) {
            throw new Exception("L'adresse email est obligatoire");
        }

        $email = strtolower(trim($email));

        // Vérifier si déjà désinscrit
        if ($this->isUnsubscribed($email)) {
            return false;
        }

        $unsubscribed = $this->getAll();

        $entry = [
            'email' => $email,
            'date' => date('Y-m-d H:i:s'),
            'reason' => $reason,
            'source' => $source, // 'public', 'manual', 'import'
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ];

        $unsubscribed[] = $entry;
        $this->fileManager->writeJson($this->unsubscribedFile, $unsubscribed);

        // Marquer le contact comme désinscrit s'il existe
        $contactModel = new Contact();
        $contact = $contactModel->getByEmail($email);
        if ($contact) {
            $contactModel->update($contact['id'], ['statut' => 'unsubscribed']);
        }

        $this->logger->info('Désinscription ajoutée', [
            'email' => $email,
            'source' => $source
        ]);

        return true;
    }

    /**
     * Retire un email de la liste des désinscrits (réactivation)
     */
    public function remove($email)
    {
        $unsubscribed = $this->getAll();
        $email = strtolower(trim($email));

        $filtered = array_filter($unsubscribed, function($entry) use ($email) {
            return strtolower($entry['email']) !== $email;
        });

        if (count($filtered) === count($unsubscribed)) {
            return false;
        }

        $this->fileManager->writeJson($this->unsubscribedFile, array_values($filtered));

        // Réactiver le contact s'il existe
        $contactModel = new Contact();
        $contact = $contactModel->getByEmail($email);
        if ($contact && $contact['statut'] === 'unsubscribed') {
            $contactModel->update($contact['id'], ['statut' => 'active']);
        }

        $this->logger->info('Désinscription retirée (réactivation)', ['email' => $email]);

        return true;
    }

    /**
     * Recherche dans les désinscrits
     */
    public function search($filters = [])
    {
        $unsubscribed = $this->getAll();

        // Filtrer par recherche de texte
        if (!empty($filters['search'])) {
            $search = strtolower($filters['search']);
            $unsubscribed = array_filter($unsubscribed, function($entry) use ($search) {
                return stripos($entry['email'], $search) !== false ||
                       stripos($entry['reason'], $search) !== false;
            });
        }

        // Filtrer par source
        if (!empty($filters['source'])) {
            $unsubscribed = array_filter($unsubscribed, function($entry) use ($filters) {
                return $entry['source'] === $filters['source'];
            });
        }

        // Filtrer par date
        if (!empty($filters['date_from'])) {
            $unsubscribed = array_filter($unsubscribed, function($entry) use ($filters) {
                return $entry['date'] >= $filters['date_from'];
            });
        }

        if (!empty($filters['date_to'])) {
            $unsubscribed = array_filter($unsubscribed, function($entry) use ($filters) {
                return $entry['date'] <= $filters['date_to'];
            });
        }

        return array_values($unsubscribed);
    }

    /**
     * Obtient les statistiques sur les désinscriptions
     */
    public function getStats()
    {
        $unsubscribed = $this->getAll();

        $stats = [
            'total' => count($unsubscribed),
            'by_source' => [],
            'by_date' => [],
            'recent' => []
        ];

        foreach ($unsubscribed as $entry) {
            // Par source
            if (!isset($stats['by_source'][$entry['source']])) {
                $stats['by_source'][$entry['source']] = 0;
            }
            $stats['by_source'][$entry['source']]++;

            // Par date
            $date = substr($entry['date'], 0, 10);
            if (!isset($stats['by_date'][$date])) {
                $stats['by_date'][$date] = 0;
            }
            $stats['by_date'][$date]++;
        }

        // Les 10 dernières désinscriptions
        usort($unsubscribed, function($a, $b) {
            return strcmp($b['date'], $a['date']);
        });
        $stats['recent'] = array_slice($unsubscribed, 0, 10);

        return $stats;
    }

    /**
     * Exporte les désinscrits en CSV
     */
    public function exportToCsv()
    {
        $unsubscribed = $this->getAll();

        $csv = "Email,Date,Raison,Source,IP\n";
        foreach ($unsubscribed as $entry) {
            $csv .= sprintf(
                '"%s","%s","%s","%s","%s"' . "\n",
                $entry['email'],
                $entry['date'],
                str_replace('"', '""', $entry['reason']),
                $entry['source'],
                $entry['ip']
            );
        }

        return $csv;
    }
}
