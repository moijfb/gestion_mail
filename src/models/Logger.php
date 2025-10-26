<?php

class Logger
{
    private $fileManager;
    private $logFile;

    public function __construct($logFile = 'application.log')
    {
        $this->fileManager = new FileManager();
        $this->logFile = $logFile;
    }

    /**
     * Enregistre une entrée de log
     */
    public function log($level, $message, $context = [])
    {
        $username = isset($_SESSION['username']) ? $_SESSION['username'] : 'anonymous';
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

        $contextStr = !empty($context) ? json_encode($context, JSON_UNESCAPED_UNICODE) : '';
        $logMessage = "[$level] [$username] [$ip] $message";

        if ($contextStr) {
            $logMessage .= " | Context: $contextStr";
        }

        $this->fileManager->appendLog($this->logFile, $logMessage);
    }

    public function info($message, $context = [])
    {
        $this->log('INFO', $message, $context);
    }

    public function warning($message, $context = [])
    {
        $this->log('WARNING', $message, $context);
    }

    public function error($message, $context = [])
    {
        $this->log('ERROR', $message, $context);
    }

    public function debug($message, $context = [])
    {
        $this->log('DEBUG', $message, $context);
    }

    /**
     * Log une action d'audit
     */
    public function audit($action, $entity, $entityId, $details = [])
    {
        $auditLogger = new self('audit.log');
        $message = "Action: $action | Entity: $entity | ID: $entityId";
        $auditLogger->info($message, $details);
    }

    /**
     * Récupère les logs récents
     */
    public function getRecentLogs($lines = 100)
    {
        return $this->fileManager->readLogTail($this->logFile, $lines);
    }
}
