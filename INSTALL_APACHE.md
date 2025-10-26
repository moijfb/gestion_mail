# Guide d'installation pour Apache

## Configuration Apache pour l'application de gestion d'emails

### Option 1 : Configuration avec VirtualHost (Recommandé)

1. **Créer un fichier de configuration Apache**

Créez le fichier `/etc/apache2/sites-available/gestion-emails.conf` :

```apache
<VirtualHost *:80>
    ServerName gestion-emails.local
    ServerAlias www.gestion-emails.local

    DocumentRoot /var/www/gestion_mail/public

    <Directory /var/www/gestion_mail/public>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted

        # Activer le module rewrite
        RewriteEngine On
    </Directory>

    # Protéger le dossier data
    <Directory /var/www/gestion_mail/data>
        Require all denied
    </Directory>

    # Logs
    ErrorLog ${APACHE_LOG_DIR}/gestion-emails-error.log
    CustomLog ${APACHE_LOG_DIR}/gestion-emails-access.log combined
</VirtualHost>
```

2. **Activer les modules Apache nécessaires**

```bash
sudo a2enmod rewrite
sudo a2enmod headers
```

3. **Activer le site**

```bash
sudo a2ensite gestion-emails.conf
sudo systemctl reload apache2
```

4. **Ajouter l'entrée dans /etc/hosts**

```bash
sudo nano /etc/hosts
```

Ajouter la ligne :
```
127.0.0.1   gestion-emails.local
```

5. **Accéder à l'application**

Ouvrir dans votre navigateur : `http://gestion-emails.local`

---

### Option 2 : Installation dans un sous-dossier

Si vous souhaitez installer l'application dans un sous-dossier d'un site existant :

1. **Copier l'application**

```bash
sudo cp -r /home/user/gestion_mail /var/www/html/gestion-emails
```

2. **Définir les permissions**

```bash
sudo chown -R www-data:www-data /var/www/html/gestion-emails
sudo chmod -R 755 /var/www/html/gestion-emails
sudo chmod -R 775 /var/www/html/gestion-emails/data
```

3. **Accéder à l'application**

Ouvrir dans votre navigateur : `http://localhost/gestion-emails/public`

---

### Option 3 : Serveur Apache local (développement)

Pour tester rapidement avec le serveur PHP intégré :

```bash
cd /home/user/gestion_mail/public
php -S localhost:8000
```

Puis ouvrir : `http://localhost:8000`

---

## Vérifications importantes

### 1. Vérifier la version de PHP

```bash
php -v
```

Doit être PHP 8.0 ou supérieur.

### 2. Vérifier les extensions PHP requises

```bash
php -m | grep -E "json|zip|session"
```

### 3. Vérifier les permissions

```bash
ls -la /var/www/gestion_mail/data
```

Le dossier `data/` doit être accessible en écriture par Apache (www-data).

### 4. Tester la configuration Apache

```bash
sudo apache2ctl configtest
```

Doit retourner "Syntax OK".

---

## Structure de l'application pour Apache

```
gestion_mail/
├── public/              ← DocumentRoot Apache (SEUL dossier public)
│   ├── index.php       ← Point d'entrée
│   ├── .htaccess       ← Règles de réécriture
│   ├── css/
│   │   └── style.css   ← Styles CSS
│   └── js/
│       └── app.js      ← JavaScript
├── src/                 ← Code PHP (MVC)
├── config/              ← Configuration
├── data/                ← Données (PROTÉGÉ)
│   └── .htaccess       ← Deny from all
└── README.md
```

---

## Résolution des problèmes courants

### Erreur 404 sur toutes les pages sauf l'accueil

**Cause** : Le module `mod_rewrite` n'est pas activé

**Solution** :
```bash
sudo a2enmod rewrite
sudo systemctl restart apache2
```

### Erreur 500 Internal Server Error

**Cause 1** : AllowOverride pas activé

**Solution** : Vérifier que `AllowOverride All` est présent dans la config Apache

**Cause 2** : Erreur PHP

**Solution** : Consulter les logs
```bash
sudo tail -f /var/log/apache2/error.log
# ou
tail -f /var/www/gestion_mail/data/logs/php_errors.log
```

### Impossible de créer des contacts

**Cause** : Permissions sur le dossier data/

**Solution** :
```bash
sudo chown -R www-data:www-data /var/www/gestion_mail/data
sudo chmod -R 775 /var/www/gestion_mail/data
```

### Les CSS ne se chargent pas

**Cause** : DocumentRoot mal configuré

**Solution** : Vérifier que DocumentRoot pointe vers le dossier `public/`

### Page de login en boucle

**Cause** : Sessions PHP non fonctionnelles

**Solution** :
```bash
# Vérifier le dossier de sessions
php -i | grep session.save_path

# Donner les permissions si nécessaire
sudo chmod 733 /var/lib/php/sessions
```

---

## Sécurité - Production

Pour un déploiement en production :

1. **Activer HTTPS**
```apache
<VirtualHost *:443>
    ServerName gestion-emails.com

    SSLEngine on
    SSLCertificateFile /path/to/cert.pem
    SSLCertificateKeyFile /path/to/key.pem

    DocumentRoot /var/www/gestion_mail/public
    # ... reste de la configuration
</VirtualHost>
```

2. **Modifier config/config.php**
```php
// Activer le flag secure pour HTTPS
ini_set('session.cookie_secure', 1);
```

3. **Désactiver les erreurs PHP en production**
```php
ini_set('display_errors', 0);
```

4. **Protéger les fichiers sensibles**
```bash
sudo chmod 600 /var/www/gestion_mail/data/*.json
```

---

## Test de l'installation

1. **Accéder à la page de login**
   - URL : `http://votre-domaine/login`
   - Vous devez voir le formulaire de connexion

2. **Se connecter**
   - Utilisateur : `admin`
   - Mot de passe : `admin123`

3. **Accéder au tableau de bord**
   - Vous devez voir les statistiques

4. **Tester la création d'un contact**
   - Contacts > Nouveau contact
   - Remplir le formulaire
   - Vérifier que le fichier `data/contacts.json` est créé

5. **Tester la page publique de désinscription**
   - URL : `http://votre-domaine/unsubscribe`
   - Vous devez voir le formulaire de désinscription

---

## Support

En cas de problème :
1. Consulter les logs Apache : `sudo tail -f /var/log/apache2/error.log`
2. Consulter les logs PHP : `tail -f data/logs/php_errors.log`
3. Consulter les logs applicatifs : `tail -f data/logs/application.log`
