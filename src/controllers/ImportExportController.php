<?php

require_once BASE_PATH . '/src/controllers/BaseController.php';

class ImportExportController extends BaseController
{
    private $contactModel;
    private $settings;

    public function __construct()
    {
        parent::__construct();
        $this->contactModel = new Contact();
        $this->settings = new Settings();
    }

    public function handle($uri, $method)
    {
        if (strpos($uri, '/import') === 0) {
            if ($method === 'POST') {
                $this->processImport();
            } else {
                $this->showImportForm();
            }
        } elseif (strpos($uri, '/export') === 0) {
            $this->exportContacts();
        }
    }

    /**
     * Affiche le formulaire d'import
     */
    private function showImportForm()
    {
        $groupModel = new Group();
        $groups = $groupModel->getAll();

        $data = [
            'groups' => $groups,
            'page_title' => 'Importer des contacts'
        ];

        $this->renderWithLayout('import/form', $data);
    }

    /**
     * Traite l'import CSV
     */
    private function processImport()
    {
        $this->verifyCsrf();

        if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
            $this->setFlash('error', 'Erreur lors du téléchargement du fichier');
            $this->redirect('/import');
        }

        $postData = $this->getPostData();
        $separator = $postData['separator'] ?? ',';
        $hasHeader = isset($postData['has_header']);
        $duplicateAction = $postData['duplicate_action'] ?? 'ignore';
        $defaultGroup = $postData['default_group'] ?? null;

        try {
            $file = fopen($_FILES['csv_file']['tmp_name'], 'r');

            if (!$file) {
                throw new Exception("Impossible d'ouvrir le fichier");
            }

            $headers = null;
            $mapping = $postData['mapping'] ?? [];
            $imported = 0;
            $skipped = 0;
            $errors = [];

            while (($row = fgetcsv($file, 0, $separator)) !== false) {
                // Première ligne = headers
                if ($headers === null) {
                    if ($hasHeader) {
                        $headers = $row;
                        continue;
                    } else {
                        $headers = array_keys($mapping);
                    }
                }

                // Mapper les colonnes
                $contactData = [];
                foreach ($mapping as $csvCol => $contactField) {
                    $colIndex = array_search($csvCol, $headers);
                    if ($colIndex !== false && isset($row[$colIndex])) {
                        $contactData[$contactField] = trim($row[$colIndex]);
                    }
                }

                // Vérifier que l'email est présent
                if (empty($contactData['email'])) {
                    $skipped++;
                    continue;
                }

                // Vérifier les doublons
                $existing = $this->contactModel->getByEmail($contactData['email']);

                if ($existing) {
                    if ($duplicateAction === 'ignore') {
                        $skipped++;
                        continue;
                    } elseif ($duplicateAction === 'update') {
                        try {
                            $this->contactModel->update($existing['id'], $contactData);
                            $imported++;
                        } catch (Exception $e) {
                            $errors[] = "Erreur mise à jour {$contactData['email']}: " . $e->getMessage();
                            $skipped++;
                        }
                        continue;
                    }
                    // Si 'create', on continue avec la création
                }

                // Ajouter au groupe par défaut si spécifié
                if ($defaultGroup) {
                    $contactData['groupes'] = [$defaultGroup];
                }

                // Créer le contact
                try {
                    $this->contactModel->create($contactData);
                    $imported++;
                } catch (Exception $e) {
                    $errors[] = "Erreur création {$contactData['email']}: " . $e->getMessage();
                    $skipped++;
                }
            }

            fclose($file);

            $this->logger->info('Import CSV terminé', [
                'imported' => $imported,
                'skipped' => $skipped,
                'errors' => count($errors)
            ]);

            $message = "Import terminé: $imported contacts importés, $skipped ignorés";
            if (!empty($errors)) {
                $message .= ". Erreurs: " . implode(', ', array_slice($errors, 0, 5));
            }

            $this->setFlash('success', $message);
            $this->redirect('/contacts');

        } catch (Exception $e) {
            $this->logger->error('Erreur import CSV', ['error' => $e->getMessage()]);
            $this->setFlash('error', 'Erreur lors de l\'import: ' . $e->getMessage());
            $this->redirect('/import');
        }
    }

    /**
     * Exporte les contacts en CSV
     */
    private function exportContacts()
    {
        $filters = $_GET;
        $contacts = $this->contactModel->search($filters);

        $separator = $this->settings->get('separator', ';');

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="contacts_' . date('Y-m-d') . '.csv"');

        $output = fopen('php://output', 'w');

        // BOM UTF-8 pour Excel
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

        // Headers
        $headers = ['Email', 'Nom', 'Prénom', 'Société', 'Téléphone', 'Tags', 'Groupes', 'Statut', 'Date ajout', 'Notes'];
        fputcsv($output, $headers, $separator);

        // Données
        foreach ($contacts as $contact) {
            $row = [
                $contact['email'],
                $contact['nom'],
                $contact['prenom'],
                $contact['societe'],
                $contact['telephone'],
                implode(', ', $contact['tags']),
                implode(', ', $contact['groupes']),
                $contact['statut'],
                $contact['date_added'],
                $contact['notes']
            ];
            fputcsv($output, $row, $separator);
        }

        fclose($output);

        $this->logger->info('Export CSV', ['count' => count($contacts)]);
        exit;
    }
}
