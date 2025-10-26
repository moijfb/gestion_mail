<?php

require_once BASE_PATH . '/src/controllers/BaseController.php';

class UnsubscriptionController extends BaseController
{
    private $unsubscribedModel;

    public function __construct()
    {
        parent::__construct();
        $this->unsubscribedModel = new Unsubscribed();
    }

    public function handle($uri, $method)
    {
        if (strpos($uri, '/unsubscriptions/export') === 0) {
            $this->exportUnsubscribed();
        } elseif (strpos($uri, '/unsubscriptions/reactivate') === 0) {
            $this->reactivate();
        } else {
            $this->listUnsubscribed();
        }
    }

    /**
     * Liste tous les désinscrits
     */
    private function listUnsubscribed()
    {
        $filters = $_GET;
        $unsubscribed = $this->unsubscribedModel->search($filters);
        $stats = $this->unsubscribedModel->getStats();

        $data = [
            'unsubscribed' => $unsubscribed,
            'stats' => $stats,
            'filters' => $filters,
            'page_title' => 'Désinscriptions'
        ];

        $this->renderWithLayout('unsubscriptions/list', $data);
    }

    /**
     * Réactive un contact désinscrit
     */
    private function reactivate()
    {
        $this->verifyCsrf();

        $email = $_GET['email'] ?? '';

        if (empty($email)) {
            $this->setFlash('error', 'Email non spécifié');
            $this->redirect('/unsubscriptions');
        }

        try {
            $this->unsubscribedModel->remove($email);
            $this->setFlash('success', 'Contact réactivé avec succès');
        } catch (Exception $e) {
            $this->setFlash('error', $e->getMessage());
        }

        $this->redirect('/unsubscriptions');
    }

    /**
     * Exporte les désinscrits en CSV
     */
    private function exportUnsubscribed()
    {
        $csv = $this->unsubscribedModel->exportToCsv();

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="unsubscribed_' . date('Y-m-d') . '.csv"');

        // BOM UTF-8 pour Excel
        echo chr(0xEF).chr(0xBB).chr(0xBF);
        echo $csv;

        $this->logger->info('Export désinscrits CSV');
        exit;
    }
}
