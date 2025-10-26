<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Désinscription - Gestion Emails</title>
    <link rel="stylesheet" href="<?= dirname($_SERVER['SCRIPT_NAME']) ?>/css/style.css">
</head>
<body class="public-page">
    <div class="public-container">
        <div class="public-box">
            <h1>Désinscription</h1>

            <?php if ($success): ?>
                <div class="flash flash-success">
                    Votre demande de désinscription a été prise en compte. Vous ne recevrez plus d'emails de notre part.
                </div>
                <p><a href="<?= dirname($_SERVER['SCRIPT_NAME']) ?>/unsubscribe">Nouvelle désinscription</a></p>
            <?php else: ?>
                <?php if ($error): ?>
                    <div class="flash flash-error">
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <p>Pour ne plus recevoir d'emails, veuillez saisir votre adresse email ci-dessous :</p>

                <form method="POST" action="<?= dirname($_SERVER['SCRIPT_NAME']) ?>/unsubscribe" class="form">
                    <div class="form-group">
                        <label for="email">Adresse email</label>
                        <input type="email" id="email" name="email" required autofocus>
                    </div>

                    <div class="form-group">
                        <label for="reason">Raison (optionnel)</label>
                        <textarea id="reason" name="reason" rows="3" placeholder="Pourquoi vous désinscrivez-vous ?"></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block">Me désinscrire</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
