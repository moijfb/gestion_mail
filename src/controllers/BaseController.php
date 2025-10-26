<?php

class BaseController
{
    protected $user;
    protected $logger;

    public function __construct()
    {
        $this->user = new User();
        $this->logger = new Logger();
    }

    /**
     * Rend une vue
     */
    protected function render($viewName, $data = [])
    {
        extract($data);

        $viewFile = BASE_PATH . '/src/views/' . $viewName . '.php';

        if (!file_exists($viewFile)) {
            throw new Exception("Vue non trouvée: $viewName");
        }

        ob_start();
        include $viewFile;
        $content = ob_get_clean();

        return $content;
    }

    /**
     * Renvoie du JSON
     */
    protected function json($data, $statusCode = 200)
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * Redirige vers une URL
     */
    protected function redirect($url)
    {
        header('Location: ' . $this->getBaseUrl() . $url);
        exit;
    }

    /**
     * Obtient l'URL de base
     */
    protected function getBaseUrl()
    {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'];
        $scriptName = dirname($_SERVER['SCRIPT_NAME']);

        return $protocol . '://' . $host . ($scriptName !== '/' ? $scriptName : '');
    }

    /**
     * Obtient les données POST
     */
    protected function getPostData()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return [];
        }

        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';

        if (strpos($contentType, 'application/json') !== false) {
            $json = file_get_contents('php://input');
            return json_decode($json, true) ?? [];
        }

        return $_POST;
    }

    /**
     * Vérifie le token CSRF
     */
    protected function verifyCsrf()
    {
        $token = $this->getPostData()['csrf_token'] ?? $_GET['csrf_token'] ?? '';

        if (!Router::verifyCsrfToken($token)) {
            $this->json(['error' => 'Token CSRF invalide'], 403);
        }
    }

    /**
     * Définit un message flash
     */
    protected function setFlash($type, $message)
    {
        if (!isset($_SESSION['flash'])) {
            $_SESSION['flash'] = [];
        }
        $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
    }

    /**
     * Obtient et efface les messages flash
     */
    protected function getFlash()
    {
        $flash = $_SESSION['flash'] ?? [];
        unset($_SESSION['flash']);
        return $flash;
    }

    /**
     * Rend le layout principal avec une vue
     */
    protected function renderWithLayout($viewName, $data = [])
    {
        $data['content'] = $this->render($viewName, $data);
        $data['flash'] = $this->getFlash();
        $data['csrf_token'] = Router::generateCsrfToken();
        $data['current_user'] = $this->user->getCurrentUser();
        $data['base_url'] = $this->getBaseUrl();

        echo $this->render('layout', $data);
    }
}
