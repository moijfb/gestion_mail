#!/bin/bash

# Script d'installation pour l'application de gestion d'emails
# Ce script configure automatiquement l'application sur un serveur Apache

set -e

echo "========================================="
echo "Installation de l'application de gestion d'emails"
echo "========================================="
echo ""

# Vérifier si le script est exécuté en tant que root
if [ "$EUID" -ne 0 ]; then
    echo "Erreur: Ce script doit être exécuté en tant que root (utilisez sudo)"
    exit 1
fi

# Vérifier PHP
echo "1. Vérification de PHP..."
if ! command -v php &> /dev/null; then
    echo "Erreur: PHP n'est pas installé"
    echo "Installez PHP avec: sudo apt-get install php libapache2-mod-php php-zip php-json php-mbstring"
    exit 1
fi

PHP_VERSION=$(php -r 'echo PHP_VERSION;')
echo "   PHP version: $PHP_VERSION"

if php -r 'exit(version_compare(PHP_VERSION, "8.0.0", "<") ? 0 : 1);' 2>/dev/null; then
    echo "Erreur: PHP 8.0 ou supérieur est requis"
    exit 1
fi

# Vérifier Apache
echo "2. Vérification d'Apache..."
if ! command -v apache2 &> /dev/null && ! command -v httpd &> /dev/null; then
    echo "Erreur: Apache n'est pas installé"
    echo "Installez Apache avec: sudo apt-get install apache2"
    exit 1
fi

# Demander le chemin d'installation
read -p "Chemin d'installation [/var/www/gestion_mail]: " INSTALL_PATH
INSTALL_PATH=${INSTALL_PATH:-/var/www/gestion_mail}

echo "3. Installation dans: $INSTALL_PATH"

# Créer le dossier d'installation
mkdir -p "$INSTALL_PATH"

# Copier les fichiers
SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
echo "4. Copie des fichiers depuis $SCRIPT_DIR..."
cp -r "$SCRIPT_DIR"/* "$INSTALL_PATH/"

# Définir les permissions
echo "5. Configuration des permissions..."
chown -R www-data:www-data "$INSTALL_PATH"
chmod -R 755 "$INSTALL_PATH"

# Créer et sécuriser le dossier data
mkdir -p "$INSTALL_PATH/data"/{logs,backups,snapshots,import_queue,export_queue}
chmod -R 775 "$INSTALL_PATH/data"
chown -R www-data:www-data "$INSTALL_PATH/data"

echo "6. Activation des modules Apache nécessaires..."
a2enmod rewrite
a2enmod headers

# Demander si on crée un VirtualHost
read -p "Créer un VirtualHost Apache? (o/n) [o]: " CREATE_VHOST
CREATE_VHOST=${CREATE_VHOST:-o}

if [ "$CREATE_VHOST" = "o" ] || [ "$CREATE_VHOST" = "O" ]; then
    read -p "Nom de domaine [gestion-emails.local]: " DOMAIN
    DOMAIN=${DOMAIN:-gestion-emails.local}

    VHOST_FILE="/etc/apache2/sites-available/gestion-emails.conf"

    echo "7. Création du VirtualHost: $VHOST_FILE"

    cat > "$VHOST_FILE" <<EOF
<VirtualHost *:80>
    ServerName $DOMAIN
    DocumentRoot $INSTALL_PATH/public

    <Directory $INSTALL_PATH/public>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
        RewriteEngine On
    </Directory>

    <Directory $INSTALL_PATH/data>
        Require all denied
    </Directory>

    <Directory $INSTALL_PATH/src>
        Require all denied
    </Directory>

    <Directory $INSTALL_PATH/config>
        Require all denied
    </Directory>

    ErrorLog \${APACHE_LOG_DIR}/gestion-emails-error.log
    CustomLog \${APACHE_LOG_DIR}/gestion-emails-access.log combined
</VirtualHost>
EOF

    echo "8. Activation du site..."
    a2ensite gestion-emails.conf

    echo "9. Redémarrage d'Apache..."
    systemctl reload apache2

    # Ajouter à /etc/hosts si domaine local
    if [[ "$DOMAIN" == *.local ]]; then
        if ! grep -q "$DOMAIN" /etc/hosts; then
            echo "10. Ajout de $DOMAIN à /etc/hosts..."
            echo "127.0.0.1   $DOMAIN" >> /etc/hosts
        fi
    fi

    echo ""
    echo "========================================="
    echo "Installation terminée avec succès!"
    echo "========================================="
    echo ""
    echo "Accédez à l'application: http://$DOMAIN"
    echo ""
else
    echo "7. Redémarrage d'Apache..."
    systemctl reload apache2

    echo ""
    echo "========================================="
    echo "Installation terminée avec succès!"
    echo "========================================="
    echo ""
    echo "Accédez à l'application: http://localhost/public"
    echo "ou configurez manuellement un VirtualHost"
    echo ""
fi

echo "Identifiants par défaut:"
echo "  Utilisateur: admin"
echo "  Mot de passe: admin123"
echo ""
echo "IMPORTANT: Changez le mot de passe après la première connexion!"
echo ""
echo "Documentation: $INSTALL_PATH/README.md"
echo "Configuration Apache: $INSTALL_PATH/INSTALL_APACHE.md"
echo ""
echo "Logs Apache: /var/log/apache2/gestion-emails-error.log"
echo "Logs application: $INSTALL_PATH/data/logs/"
echo ""
