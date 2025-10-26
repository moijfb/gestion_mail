<?php
/**
 * Script de test pour vérifier l'environnement
 * Accéder à cette page via : http://votre-domaine/test.php
 * SUPPRIMER CE FICHIER APRÈS INSTALLATION
 */

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test - Gestion Emails</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .test-box {
            background: white;
            padding: 20px;
            margin: 10px 0;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .success { border-left: 4px solid #27ae60; }
        .error { border-left: 4px solid #e74c3c; }
        .warning { border-left: 4px solid #f39c12; }
        h1 { color: #2c3e50; }
        h2 { color: #34495e; font-size: 1.2rem; }
        .status { font-weight: bold; }
        .status.ok { color: #27ae60; }
        .status.fail { color: #e74c3c; }
        table { width: 100%; border-collapse: collapse; }
        td { padding: 5px; border-bottom: 1px solid #ddd; }
        td:first-child { font-weight: bold; width: 200px; }
        .delete-warning {
            background: #fff3cd;
            border: 2px solid #f39c12;
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <h1>Test de l'environnement - Gestion d'Emails</h1>

    <div class="delete-warning">
        <strong>⚠️ AVERTISSEMENT DE SÉCURITÉ :</strong> Supprimez ce fichier après avoir vérifié l'installation !
        <pre>rm <?= __FILE__ ?></pre>
    </div>

    <?php
    $errors = [];
    $warnings = [];

    // Test PHP Version
    echo '<div class="test-box ' . (version_compare(PHP_VERSION, '8.0.0', '>=') ? 'success' : 'error') . '">';
    echo '<h2>Version PHP</h2>';
    echo '<table>';
    echo '<tr><td>Version détectée</td><td>' . PHP_VERSION . '</td></tr>';
    echo '<tr><td>Minimum requis</td><td>8.0.0</td></tr>';
    echo '<tr><td>Statut</td><td class="status ' . (version_compare(PHP_VERSION, '8.0.0', '>=') ? 'ok">✓ OK' : 'fail">✗ ERREUR') . '</td></tr>';
    echo '</table>';
    if (version_compare(PHP_VERSION, '8.0.0', '<')) {
        $errors[] = 'PHP 8.0 ou supérieur est requis';
    }
    echo '</div>';

    // Test Extensions PHP
    $required_extensions = ['json', 'zip', 'session', 'mbstring'];
    echo '<div class="test-box">';
    echo '<h2>Extensions PHP</h2>';
    echo '<table>';
    $all_extensions_ok = true;
    foreach ($required_extensions as $ext) {
        $loaded = extension_loaded($ext);
        if (!$loaded) {
            $all_extensions_ok = false;
            $errors[] = "Extension PHP manquante: $ext";
        }
        echo '<tr><td>' . $ext . '</td><td class="status ' . ($loaded ? 'ok">✓ Installé' : 'fail">✗ Manquant') . '</td></tr>';
    }
    echo '</table>';
    echo '</div>';

    // Test Permissions dossier data/
    $data_path = dirname(__DIR__) . '/data';
    $data_writable = is_dir($data_path) && is_writable($data_path);
    echo '<div class="test-box ' . ($data_writable ? 'success' : 'error') . '">';
    echo '<h2>Permissions dossier data/</h2>';
    echo '<table>';
    echo '<tr><td>Chemin</td><td>' . $data_path . '</td></tr>';
    echo '<tr><td>Existe</td><td class="status ' . (is_dir($data_path) ? 'ok">✓ Oui' : 'fail">✗ Non') . '</td></tr>';
    echo '<tr><td>Accessible en écriture</td><td class="status ' . ($data_writable ? 'ok">✓ Oui' : 'fail">✗ Non') . '</td></tr>';
    if (is_dir($data_path)) {
        $perms = substr(sprintf('%o', fileperms($data_path)), -4);
        echo '<tr><td>Permissions</td><td>' . $perms . '</td></tr>';
    }
    echo '</table>';
    if (!$data_writable) {
        $errors[] = 'Le dossier data/ doit être accessible en écriture';
        echo '<p>Commande pour corriger : <code>chmod -R 775 ' . $data_path . '</code></p>';
    }
    echo '</div>';

    // Test Apache mod_rewrite
    echo '<div class="test-box">';
    echo '<h2>Module Apache mod_rewrite</h2>';
    $rewrite_enabled = function_exists('apache_get_modules') && in_array('mod_rewrite', apache_get_modules());
    echo '<table>';
    echo '<tr><td>Statut</td><td class="status ' . ($rewrite_enabled ? 'ok">✓ Activé' : 'fail">? Inconnu') . '</td></tr>';
    echo '</table>';
    if (!$rewrite_enabled && function_exists('apache_get_modules')) {
        $warnings[] = 'Assurez-vous que mod_rewrite est activé : sudo a2enmod rewrite';
    }
    echo '</div>';

    // Test fichiers de configuration
    $config_file = dirname(__DIR__) . '/config/config.php';
    echo '<div class="test-box ' . (file_exists($config_file) ? 'success' : 'error') . '">';
    echo '<h2>Fichiers de configuration</h2>';
    echo '<table>';
    echo '<tr><td>config/config.php</td><td class="status ' . (file_exists($config_file) ? 'ok">✓ Existe' : 'fail">✗ Manquant') . '</td></tr>';
    echo '<tr><td>public/.htaccess</td><td class="status ' . (file_exists(__DIR__ . '/.htaccess') ? 'ok">✓ Existe' : 'fail">✗ Manquant') . '</td></tr>';
    echo '<tr><td>data/.htaccess</td><td class="status ' . (file_exists($data_path . '/.htaccess') ? 'ok">✓ Existe' : 'fail">✗ Manquant') . '</td></tr>';
    echo '</table>';
    echo '</div>';

    // Test écriture dans data/
    if ($data_writable) {
        $test_file = $data_path . '/test_write.txt';
        $write_ok = @file_put_contents($test_file, 'test') !== false;
        if ($write_ok) {
            @unlink($test_file);
        }
        echo '<div class="test-box ' . ($write_ok ? 'success' : 'error') . '">';
        echo '<h2>Test d\'écriture</h2>';
        echo '<table>';
        echo '<tr><td>Écriture dans data/</td><td class="status ' . ($write_ok ? 'ok">✓ OK' : 'fail">✗ ERREUR') . '</td></tr>';
        echo '</table>';
        if (!$write_ok) {
            $errors[] = 'Impossible d\'écrire dans le dossier data/';
        }
        echo '</div>';
    }

    // Test sessions
    @session_start();
    $session_ok = session_status() === PHP_SESSION_ACTIVE;
    echo '<div class="test-box ' . ($session_ok ? 'success' : 'error') . '">';
    echo '<h2>Sessions PHP</h2>';
    echo '<table>';
    echo '<tr><td>Statut</td><td class="status ' . ($session_ok ? 'ok">✓ Fonctionnel' : 'fail">✗ Erreur') . '</td></tr>';
    echo '<tr><td>Session ID</td><td>' . session_id() . '</td></tr>';
    echo '</table>';
    if (!$session_ok) {
        $errors[] = 'Les sessions PHP ne fonctionnent pas correctement';
    }
    echo '</div>';

    // Informations serveur
    echo '<div class="test-box">';
    echo '<h2>Informations serveur</h2>';
    echo '<table>';
    echo '<tr><td>Serveur</td><td>' . $_SERVER['SERVER_SOFTWARE'] . '</td></tr>';
    echo '<tr><td>Document Root</td><td>' . $_SERVER['DOCUMENT_ROOT'] . '</td></tr>';
    echo '<tr><td>Script Filename</td><td>' . __FILE__ . '</td></tr>';
    echo '<tr><td>PHP SAPI</td><td>' . php_sapi_name() . '</td></tr>';
    echo '</table>';
    echo '</div>';

    // Résumé
    echo '<div class="test-box ' . (empty($errors) ? 'success' : 'error') . '">';
    echo '<h2>Résumé</h2>';
    if (empty($errors)) {
        echo '<p class="status ok">✓ Tous les tests sont passés avec succès !</p>';
        echo '<p>L\'application est prête à être utilisée.</p>';
        echo '<p><a href="/">Accéder à l\'application</a></p>';
    } else {
        echo '<p class="status fail">✗ Erreurs détectées :</p>';
        echo '<ul>';
        foreach ($errors as $error) {
            echo '<li>' . htmlspecialchars($error) . '</li>';
        }
        echo '</ul>';
    }
    if (!empty($warnings)) {
        echo '<p><strong>Avertissements :</strong></p>';
        echo '<ul>';
        foreach ($warnings as $warning) {
            echo '<li>' . htmlspecialchars($warning) . '</li>';
        }
        echo '</ul>';
    }
    echo '</div>';
    ?>

    <div class="test-box">
        <h2>Étapes suivantes</h2>
        <ol>
            <li><strong>Supprimer ce fichier de test</strong> : <code>rm <?= __FILE__ ?></code></li>
            <li>Accéder à l'application : <a href="/">http://votre-domaine/</a></li>
            <li>Se connecter avec : <strong>admin / admin123</strong></li>
            <li>Changer le mot de passe immédiatement</li>
        </ol>
    </div>
</body>
</html>
