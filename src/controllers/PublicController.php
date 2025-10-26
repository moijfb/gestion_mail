<?php

require_once BASE_PATH . '/src/controllers/BaseController.php';

class PublicController extends BaseController
{
    private $unsubscribedModel;

    public function __construct()
    {
        parent::__construct();
        $this->unsubscribedModel = new Unsubscribed();
    }

    public function handle($uri, $method)
    {
        if ($method === 'POST') {
            $this->processUnsubscribe();
        } else {
            $this->showUnsubscribeForm();
        }
    }

    /**
     * Affiche le formulaire de désinscription publique
     */
    private function showUnsubscribeForm()
    {
        $success = isset($_GET['success']);
        $error = $_GET['error'] ?? null;

        $data = [
            'success' => $success,
            'error' => $error
        ];

        echo $this->render('public/unsubscribe', $data);
    }

    /**
     * Traite la désinscription
     */
    private function processUnsubscribe()
    {
        $postData = $this->getPostData();
        $email = trim($postData['email'] ?? '');
        $reason = trim($postData['reason'] ?? '');

        // Validation basique
        if (empty($email)) {
            $this->redirect('/unsubscribe?error=' . urlencode('Veuillez saisir votre adresse email'));
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->redirect('/unsubscribe?error=' . urlencode('Adresse email invalide'));
        }

        // Protection anti-spam basique (rate limiting par IP)
        $this->checkRateLimit();

        // Vérifier si le contact existe
        $contactModel = new Contact();
        $contact = $contactModel->getByEmail($email);

        if (!$contact) {
            // Email non trouvé, mais on affiche quand même le message de succès
            // pour ne pas révéler si l'email existe ou non
            $this->logger->info('Tentative de désinscription email non trouvé', ['email' => $email]);
            $this->redirect('/unsubscribe?success=1');
        }

        // Ajouter à la liste des désinscrits
        try {
            $this->unsubscribedModel->add($email, $reason, 'public');
            $this->logger->info('Désinscription publique', ['email' => $email]);
            $this->redirect('/unsubscribe?success=1');

        } catch (Exception $e) {
            $this->logger->error('Erreur désinscription publique', [
                'email' => $email,
                'error' => $e->getMessage()
            ]);
            $this->redirect('/unsubscribe?error=' . urlencode('Une erreur est survenue'));
        }
    }

    /**
     * Vérifie le rate limiting par IP
     */
    private function checkRateLimit()
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $rateLimitFile = DATA_PATH . '/rate_limit.json';

        $fileManager = new FileManager();
        $rateLimits = $fileManager->readJson('rate_limit.json', []);

        $now = time();
        $maxAttempts = 5;
        $timeWindow = 300; // 5 minutes

        // Nettoyer les anciennes entrées
        $rateLimits = array_filter($rateLimits, function($entry) use ($now, $timeWindow) {
            return ($now - $entry['timestamp']) < $timeWindow;
        });

        // Compter les tentatives pour cette IP
        $ipAttempts = array_filter($rateLimits, function($entry) use ($ip) {
            return $entry['ip'] === $ip;
        });

        if (count($ipAttempts) >= $maxAttempts) {
            $this->logger->warning('Rate limit dépassé', ['ip' => $ip]);
            http_response_code(429);
            die('Trop de tentatives. Veuillez réessayer dans quelques minutes.');
        }

        // Enregistrer cette tentative
        $rateLimits[] = [
            'ip' => $ip,
            'timestamp' => $now
        ];

        $fileManager->writeJson('rate_limit.json', $rateLimits, false);
    }
}
