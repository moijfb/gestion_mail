# Application de Gestion d'Emails

Application web PHP de gestion de contacts et d'emails avec stockage fichier uniquement.

## Fonctionnalités

### Gestion des contacts
- Création, modification, suppression de contacts
- Import/export CSV avec mappage de colonnes
- Détection et gestion des doublons
- Validation des emails
- Organisation par groupes et tags
- Recherche et filtrage avancés

### Groupes et tags
- Création de groupes de contacts
- Tags libres pour classification
- Affectation multiple de contacts

### Désinscriptions
- Page publique de désinscription
- Gestion administrative des désinscrits
- Historique et traçabilité
- Export CSV des désinscriptions

### Préparation d'envois
- Sélection de contacts par filtres
- Découpage automatique en lots
- Format prêt pour Outlook/CCI
- Copie en un clic

### Statistiques et logs
- Tableau de bord avec statistiques
- Répartition par domaines et groupes
- Journaux d'activité et audit
- Export des données

### Sécurité
- Authentification par mot de passe
- Sessions sécurisées
- Tokens CSRF
- Verrouillage de fichiers (flock)
- Protection des données
- Hachage bcrypt des mots de passe

## Installation

### Prérequis
- PHP 8.0 ou supérieur
- Extension PHP ZIP activée
- Serveur web (Apache, Nginx)

### Installation rapide

1. Cloner le projet
```bash
git clone <url-repo>
cd gestion_mail
```

2. Configurer le serveur web pour pointer vers le dossier `public/`

3. Vérifier les permissions sur le dossier `data/`
```bash
chmod -R 755 data/
```

4. Accéder à l'application via votre navigateur

### Connexion par défaut
- **Utilisateur**: admin
- **Mot de passe**: admin123

**Important**: Changez le mot de passe par défaut immédiatement après la première connexion.

## Utilisation

### Gestion des contacts
1. **Ajouter un contact**: Contacts > Nouveau contact
2. **Importer des contacts**: Import > Sélectionner CSV
3. **Exporter des contacts**: Contacts > Exporter la sélection

### Préparation d'envois
1. Aller sur "Préparer envoi"
2. Appliquer des filtres
3. Cliquer sur "Préparer les lots"
4. Copier les adresses pour Outlook

### Désinscriptions
**Page publique**: `/unsubscribe` - À partager dans vos emails
**Page admin**: Désinscriptions - Gérer les désinscrits

## Sécurité et RGPD
- Protection des données par `.htaccess`
- Gestion des désinscriptions
- Journaux d'audit
- Sauvegardes automatiques
- Export de données

## Support
Consulter les logs dans `data/logs/` en cas de problème.

## Version
1.0 - 26 octobre 2025