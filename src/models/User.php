<?php

class User
{
    private $fileManager;
    private $logger;
    private $usersFile = 'users.json';

    public function __construct()
    {
        $this->fileManager = new FileManager();
        $this->logger = new Logger();
        $this->initializeDefaultUser();
    }

    /**
     * Initialise l'utilisateur par défaut si aucun utilisateur n'existe
     */
    private function initializeDefaultUser()
    {
        $users = $this->fileManager->readJson($this->usersFile, []);

        if (empty($users)) {
            // Créer un utilisateur par défaut : admin / admin123
            $defaultUser = [
                'username' => 'admin',
                'password_hash' => password_hash('admin123', PASSWORD_BCRYPT),
                'date_created' => date('Y-m-d H:i:s'),
                'last_login' => null
            ];

            $users[] = $defaultUser;
            $this->fileManager->writeJson($this->usersFile, $users, false);

            $this->logger->info('Utilisateur par défaut créé', ['username' => 'admin']);
        }
    }

    /**
     * Authentifie un utilisateur
     */
    public function authenticate($username, $password)
    {
        $users = $this->fileManager->readJson($this->usersFile, []);

        foreach ($users as $index => $user) {
            if ($user['username'] === $username) {
                if (password_verify($password, $user['password_hash'])) {
                    // Mettre à jour la dernière connexion
                    $users[$index]['last_login'] = date('Y-m-d H:i:s');
                    $this->fileManager->writeJson($this->usersFile, $users);

                    // Créer la session
                    $_SESSION['authenticated'] = true;
                    $_SESSION['username'] = $username;
                    $_SESSION['login_time'] = time();

                    // Régénérer l'ID de session pour sécurité
                    session_regenerate_id(true);

                    $this->logger->info('Connexion réussie', ['username' => $username]);

                    return true;
                }

                $this->logger->warning('Échec d\'authentification - mot de passe incorrect', ['username' => $username]);
                return false;
            }
        }

        $this->logger->warning('Échec d\'authentification - utilisateur non trouvé', ['username' => $username]);
        return false;
    }

    /**
     * Vérifie si l'utilisateur est authentifié
     */
    public function isAuthenticated()
    {
        if (!isset($_SESSION['authenticated']) || $_SESSION['authenticated'] !== true) {
            return false;
        }

        // Vérifier la durée de la session (30 minutes)
        if (isset($_SESSION['login_time']) && (time() - $_SESSION['login_time']) > 1800) {
            $this->logout();
            return false;
        }

        // Rafraîchir le temps de session
        $_SESSION['login_time'] = time();

        return true;
    }

    /**
     * Déconnecte l'utilisateur
     */
    public function logout()
    {
        $username = $_SESSION['username'] ?? 'unknown';

        $_SESSION = [];

        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 3600, '/');
        }

        session_destroy();

        $this->logger->info('Déconnexion', ['username' => $username]);
    }

    /**
     * Change le mot de passe de l'utilisateur
     */
    public function changePassword($username, $oldPassword, $newPassword)
    {
        $users = $this->fileManager->readJson($this->usersFile, []);

        foreach ($users as $index => $user) {
            if ($user['username'] === $username) {
                // Vérifier l'ancien mot de passe
                if (!password_verify($oldPassword, $user['password_hash'])) {
                    $this->logger->warning('Échec de changement de mot de passe - ancien mot de passe incorrect', ['username' => $username]);
                    return false;
                }

                // Valider le nouveau mot de passe
                if (strlen($newPassword) < 6) {
                    return false;
                }

                // Mettre à jour le mot de passe
                $users[$index]['password_hash'] = password_hash($newPassword, PASSWORD_BCRYPT);
                $this->fileManager->writeJson($this->usersFile, $users);

                $this->logger->info('Mot de passe changé avec succès', ['username' => $username]);

                return true;
            }
        }

        return false;
    }

    /**
     * Crée un nouveau utilisateur
     */
    public function createUser($username, $password)
    {
        $users = $this->fileManager->readJson($this->usersFile, []);

        // Vérifier si l'utilisateur existe déjà
        foreach ($users as $user) {
            if ($user['username'] === $username) {
                return false;
            }
        }

        $newUser = [
            'username' => $username,
            'password_hash' => password_hash($password, PASSWORD_BCRYPT),
            'date_created' => date('Y-m-d H:i:s'),
            'last_login' => null
        ];

        $users[] = $newUser;
        $this->fileManager->writeJson($this->usersFile, $users);

        $this->logger->info('Nouvel utilisateur créé', ['username' => $username]);

        return true;
    }

    /**
     * Obtient les informations de l'utilisateur courant
     */
    public function getCurrentUser()
    {
        if (!$this->isAuthenticated()) {
            return null;
        }

        $username = $_SESSION['username'];
        $users = $this->fileManager->readJson($this->usersFile, []);

        foreach ($users as $user) {
            if ($user['username'] === $username) {
                unset($user['password_hash']); // Ne pas retourner le hash
                return $user;
            }
        }

        return null;
    }
}
