<?php

require_once BASE_PATH . '/src/controllers/BaseController.php';

class AuthController extends BaseController
{
    public function handle($uri, $method)
    {
        if (strpos($uri, '/logout') === 0) {
            $this->logout();
        } elseif (strpos($uri, '/login') === 0) {
            if ($method === 'POST') {
                $this->processLogin();
            } else {
                $this->showLogin();
            }
        }
    }

    /**
     * Affiche la page de connexion
     */
    private function showLogin()
    {
        // Si déjà connecté, rediriger vers le dashboard
        if ($this->user->isAuthenticated()) {
            $this->redirect('/dashboard');
        }

        $data = [
            'error' => $_GET['error'] ?? null,
            'csrf_token' => Router::generateCsrfToken()
        ];

        echo $this->render('login', $data);
    }

    /**
     * Traite la connexion
     */
    private function processLogin()
    {
        $postData = $this->getPostData();

        $username = $postData['username'] ?? '';
        $password = $postData['password'] ?? '';

        if (empty($username) || empty($password)) {
            $this->redirect('/login?error=Veuillez+remplir+tous+les+champs');
        }

        if ($this->user->authenticate($username, $password)) {
            $this->redirect('/dashboard');
        } else {
            $this->redirect('/login?error=Identifiants+incorrects');
        }
    }

    /**
     * Déconnecte l'utilisateur
     */
    private function logout()
    {
        $this->user->logout();
        $this->redirect('/login');
    }
}
