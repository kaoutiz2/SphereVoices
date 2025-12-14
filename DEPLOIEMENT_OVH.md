# Guide de Déploiement OVH - SphereVoices

## 📋 Structure du Projet

Le projet est maintenant structuré directement pour OVH :

```
Projet (racine)/
├── www/              # Racine web publique (DocumentRoot OVH)
│   ├── index.php
│   ├── .htaccess
│   ├── core/
│   ├── modules/
│   ├── themes/
│   └── sites/
├── vendor/           # Dépendances Composer (au même niveau que www/)
├── config/           # Configuration Drupal (au même niveau que www/)
├── composer.json
└── .env.production   # Configuration de production (à créer)
```

## 🚀 Déploiement via Git (Recommandé)

### Configuration OVH pour Git

1. **Dans votre espace client OVH :**
   - Allez dans : Hébergement → Git
   - Configurez le dépôt Git avec votre URL
   - Définissez le dossier de déploiement : `/www` (ou laissez vide si vous voulez déployer à la racine)

2. **Structure sur OVH après déploiement Git :**
   ```
   FTP OVH (racine)/
   ├── www/              ← Contenu du dossier www/ du repo
   ├── vendor/           ← Contenu du dossier vendor/ du repo
   ├── config/           ← Contenu du dossier config/ du repo
   └── .env.production   ← À créer manuellement avec vos paramètres OVH
   ```

### Première configuration

1. **Après le premier déploiement Git :**
   - Créez le fichier `.env.production` à la racine du FTP (même niveau que `www/`)
   - Ajoutez vos paramètres de base de données :
     ```env
     DB_DRIVER=mysql
     DB_HOST=spheree921.mysql.db
     DB_PORT=3306
     DB_NAME=spheree921
     DB_USER=spheree921
     DB_PASSWORD=Cameroun2026
     DB_PREFIX=
     DB_COLLATION=utf8mb4_general_ci
     ```

2. **Vérifiez les permissions :**
   - `www/sites/default/files/` → 755 ou 777

3. **Videz le cache :**
   - Supprimez `www/sites/default/files/php/twig/` et `www/sites/default/files/css/`

## 📝 Configuration de la base de données

Le fichier `settings.php` charge automatiquement les variables depuis `.env.production` quand vous êtes sur `www.spherevoices.com`.

### Créer le fichier .env.production

1. **Sur votre FTP OVH**, créez `.env.production` à la racine (même niveau que `www/` et `vendor/`)

2. **Ajoutez vos paramètres OVH :**
   ```env
   DB_DRIVER=mysql
   DB_HOST=spheree921.mysql.db
   DB_PORT=3306
   DB_NAME=spheree921
   DB_USER=spheree921
   DB_PASSWORD=Cameroun2026
   DB_PREFIX=
   DB_COLLATION=utf8mb4_general_ci
   ```

   **Où trouver ces informations :**
   - Espace client OVH → Hébergement → Bases de données
   - Ou dans les emails de création de base de données

## ⚙️ Mises à jour

Pour mettre à jour le site après un push Git :

1. **OVH déploie automatiquement** depuis votre repo Git
2. **Videz le cache** si nécessaire :
   - Via Drush (si disponible) : `drush cr`
   - Ou supprimez manuellement : `www/sites/default/files/php/twig/`

## ⚠️ Fichiers à NE PAS versionner

Assurez-vous que ces fichiers sont dans `.gitignore` :
- `.env.production` (contient les mots de passe)
- `www/sites/default/settings.php` (peut contenir des infos sensibles)
- `www/sites/default/files/` (fichiers uploadés)

## 🔧 Commandes utiles

Si vous avez accès SSH sur OVH :

```bash
# Vider le cache
drush cr

# Mettre à jour la base de données
drush updb

# Importer la configuration
drush config:import

# Vérifier les permissions
ls -la www/sites/default/files/
```

## 📋 Checklist de déploiement

- [ ] Repo Git configuré sur OVH
- [ ] Premier déploiement effectué
- [ ] Fichier `.env.production` créé avec les bonnes valeurs
- [ ] Permissions correctes sur `www/sites/default/files/`
- [ ] Cache vidé
- [ ] Site testé et fonctionnel
