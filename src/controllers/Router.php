<?php

class Router
{
    private $user;

    public function __construct()
    {
        $this->user = new User();
    }

    /**
     * Gère les requêtes HTTP
     */
    public function handleRequest()
    {
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $method = $_SERVER['REQUEST_METHOD'];

        // Retirer le préfixe du chemin si l'app est dans un sous-dossier
        $scriptName = dirname($_SERVER['SCRIPT_NAME']);
        if ($scriptName !== '/') {
            $uri = str_replace($scriptName, '', $uri);
        }

        // Routes publiques (sans authentification)
        $publicRoutes = [
            '/login' => 'AuthController',
            '/unsubscribe' => 'PublicController',
            '/public/unsubscribe' => 'PublicController'
        ];

        foreach ($publicRoutes as $route => $controller) {
            if (strpos($uri, $route) === 0) {
                $this->loadController($controller, $uri, $method);
                return;
            }
        }

        // Vérifier l'authentification pour les autres routes
        if (!$this->user->isAuthenticated()) {
            header('Location: ' . $this->getBaseUrl() . '/login');
            exit;
        }

        // Routes protégées
        $routes = [
            '/logout' => 'AuthController',
            '/dashboard' => 'DashboardController',
            '/contacts' => 'ContactController',
            '/groups' => 'GroupController',
            '/import' => 'ImportExportController',
            '/export' => 'ImportExportController',
            '/unsubscriptions' => 'UnsubscriptionController',
            '/composer' => 'ComposerController',
            '/settings' => 'SettingsController',
            '/logs' => 'LogController'
        ];

        foreach ($routes as $route => $controller) {
            if (strpos($uri, $route) === 0) {
                $this->loadController($controller, $uri, $method);
                return;
            }
        }

        // Route par défaut
        if ($uri === '/' || $uri === '') {
            header('Location: ' . $this->getBaseUrl() . '/dashboard');
            exit;
        }

        // 404
        http_response_code(404);
        echo "Page non trouvée";
    }

    /**
     * Charge et exécute un contrôleur
     */
    private function loadController($controllerName, $uri, $method)
    {
        $controllerFile = BASE_PATH . '/src/controllers/' . $controllerName . '.php';

        if (!file_exists($controllerFile)) {
            http_response_code(500);
            echo "Erreur: Contrôleur non trouvé";
            return;
        }

        require_once $controllerFile;

        $controller = new $controllerName();

        if (method_exists($controller, 'handle')) {
            $controller->handle($uri, $method);
        } else {
            http_response_code(500);
            echo "Erreur: Méthode handle non trouvée";
        }
    }

    /**
     * Obtient l'URL de base
     */
    private function getBaseUrl()
    {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'];
        $scriptName = dirname($_SERVER['SCRIPT_NAME']);

        return $protocol . '://' . $host . ($scriptName !== '/' ? $scriptName : '');
    }

    /**
     * Génère un token CSRF
     */
    public static function generateCsrfToken()
    {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    /**
     * Vérifie le token CSRF
     */
    public static function verifyCsrfToken($token)
    {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
}
