<?php

require_once BASE_PATH . '/src/controllers/BaseController.php';

class DashboardController extends BaseController
{
    public function handle($uri, $method)
    {
        $this->showDashboard();
    }

    /**
     * Affiche le tableau de bord
     */
    private function showDashboard()
    {
        $contactModel = new Contact();
        $groupModel = new Group();
        $unsubscribedModel = new Unsubscribed();

        $contactStats = $contactModel->getStats();
        $groupStats = $groupModel->getStats();
        $unsubscribedStats = $unsubscribedModel->getStats();

        // Trier les groupes par nombre de contacts
        uasort($groupStats, function($a, $b) {
            return $b['count'] - $a['count'];
        });

        $data = [
            'contact_stats' => $contactStats,
            'group_stats' => $groupStats,
            'unsubscribed_stats' => $unsubscribedStats,
            'top_groups' => array_slice($groupStats, 0, 10, true),
            'page_title' => 'Tableau de bord'
        ];

        $this->renderWithLayout('dashboard', $data);
    }
}
