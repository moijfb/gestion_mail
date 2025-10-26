<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title ?? 'Gestion Emails') ?></title>
    <link rel="stylesheet" href="<?= $base_url ?>/css/style.css">
</head>
<body>
    <div class="container">
        <header>
            <div class="header-content">
                <h1>Gestion d'Emails</h1>
                <div class="user-menu">
                    <span>Connecté: <?= htmlspecialchars($current_user['username'] ?? 'Utilisateur') ?></span>
                    <a href="<?= $base_url ?>/logout" class="btn btn-sm">Déconnexion</a>
                </div>
            </div>
            <nav>
                <a href="<?= $base_url ?>/dashboard">Tableau de bord</a>
                <a href="<?= $base_url ?>/contacts">Contacts</a>
                <a href="<?= $base_url ?>/groups">Groupes</a>
                <a href="<?= $base_url ?>/composer">Préparer envoi</a>
                <a href="<?= $base_url ?>/import">Import</a>
                <a href="<?= $base_url ?>/export">Export</a>
                <a href="<?= $base_url ?>/unsubscriptions">Désinscriptions</a>
                <a href="<?= $base_url ?>/logs">Logs</a>
                <a href="<?= $base_url ?>/settings">Paramètres</a>
            </nav>
        </header>

        <main>
            <?php if (!empty($flash)): ?>
                <div class="flash-messages">
                    <?php foreach ($flash as $message): ?>
                        <div class="flash flash-<?= htmlspecialchars($message['type']) ?>">
                            <?= htmlspecialchars($message['message']) ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?= $content ?>
        </main>

        <footer>
            <p>&copy; <?= date('Y') ?> - Gestion d'Emails - <a href="<?= $base_url ?>/unsubscribe" target="_blank">Page de désinscription publique</a></p>
        </footer>
    </div>

    <script src="<?= $base_url ?>/js/app.js"></script>
</body>
</html>
