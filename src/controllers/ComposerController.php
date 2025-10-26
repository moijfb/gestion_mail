<?php

require_once BASE_PATH . '/src/controllers/BaseController.php';

class ComposerController extends BaseController
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
        if (strpos($uri, '/composer/prepare') === 0 && $method === 'POST') {
            $this->prepareBatches();
        } else {
            $this->showComposer();
        }
    }

    /**
     * Affiche la page de préparation d'envois
     */
    private function showComposer()
    {
        $groupModel = new Group();
        $groups = $groupModel->getAll();

        // Récupérer tous les tags uniques
        $allTags = [];
        foreach ($this->contactModel->getAll() as $contact) {
            $allTags = array_merge($allTags, $contact['tags']);
        }
        $allTags = array_unique($allTags);
        sort($allTags);

        $data = [
            'groups' => $groups,
            'all_tags' => $allTags,
            'page_title' => 'Préparer un envoi',
            'max_per_send' => $this->settings->get('max_per_send', 50),
            'separator' => $this->settings->get('separator', ';')
        ];

        $this->renderWithLayout('composer/index', $data);
    }

    /**
     * Prépare les lots d'envoi
     */
    private function prepareBatches()
    {
        $postData = $this->getPostData();
        $filters = $postData;

        // Récupérer les contacts selon les filtres
        $contacts = $this->contactModel->search($filters);

        // Exclure les désinscrits
        $unsubscribedModel = new Unsubscribed();
        $contacts = array_filter($contacts, function($contact) use ($unsubscribedModel) {
            return !$unsubscribedModel->isUnsubscribed($contact['email']) &&
                   $contact['statut'] === 'active';
        });

        $contacts = array_values($contacts);

        // Paramètres de découpage
        $maxPerSend = $this->settings->get('max_per_send', 50);
        $separator = $this->settings->get('separator', ';');
        $pauseBetweenBatches = $this->settings->get('pause_between_batches', 0);

        // Découper en lots
        $batches = [];
        $batchNumber = 1;

        for ($i = 0; $i < count($contacts); $i += $maxPerSend) {
            $batchContacts = array_slice($contacts, $i, $maxPerSend);
            $emails = array_map(function($c) { return $c['email']; }, $batchContacts);

            $batches[] = [
                'number' => $batchNumber,
                'contacts' => $batchContacts,
                'emails' => $emails,
                'emails_string' => implode($separator . ' ', $emails),
                'count' => count($batchContacts)
            ];

            $batchNumber++;
        }

        $data = [
            'batches' => $batches,
            'total_contacts' => count($contacts),
            'total_batches' => count($batches),
            'max_per_send' => $maxPerSend,
            'separator' => $separator,
            'pause_between_batches' => $pauseBetweenBatches,
            'page_title' => 'Lots d\'envoi préparés'
        ];

        $this->logger->info('Lots d\'envoi préparés', [
            'total_contacts' => count($contacts),
            'total_batches' => count($batches)
        ]);

        $this->renderWithLayout('composer/batches', $data);
    }
}
