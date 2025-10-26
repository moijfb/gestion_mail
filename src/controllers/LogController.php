<?php

require_once BASE_PATH . '/src/controllers/BaseController.php';

class LogController extends BaseController
{
    public function handle($uri, $method)
    {
        $this->showLogs();
    }

    /**
     * Affiche les logs
     */
    private function showLogs()
    {
        $logType = $_GET['type'] ?? 'application';
        $lines = $_GET['lines'] ?? 100;

        $logger = new Logger($logType . '.log');
        $logs = $logger->getRecentLogs($lines);

        // Logs disponibles
        $availableLogs = [];
        $logFiles = glob(LOGS_PATH . '/*.log');
        foreach ($logFiles as $file) {
            $availableLogs[] = basename($file, '.log');
        }

        $data = [
            'logs' => $logs,
            'log_type' => $logType,
            'available_logs' => $availableLogs,
            'lines' => $lines,
            'page_title' => 'Journaux d\'activité'
        ];

        $this->renderWithLayout('logs/index', $data);
    }
}
