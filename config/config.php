<?php
// Configuration de l'application
define('BASE_PATH', dirname(__DIR__));
define('DATA_PATH', BASE_PATH . '/data');
define('LOGS_PATH', DATA_PATH . '/logs');
define('IMPORT_PATH', DATA_PATH . '/import_queue');
define('EXPORT_PATH', DATA_PATH . '/export_queue');

// Configuration de session
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // Mettre à 1 si HTTPS
ini_set('session.cookie_samesite', 'Strict');

// Durée de session (30 minutes)
ini_set('session.gc_maxlifetime', 1800);
session_set_cookie_params(1800);

// Timezone
date_default_timezone_set('Europe/Paris');

// Gestion des erreurs
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', LOGS_PATH . '/php_errors.log');

// Autoloader simple
spl_autoload_register(function ($class) {
    $file = BASE_PATH . '/src/' . str_replace('\\', '/', $class) . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});
