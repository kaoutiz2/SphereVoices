# Solution Erreur 500 - Problème d'autoloader

## 🔍 Problème identifié

L'erreur indique que l'autoloader de Composer cherche `web/core/` au lieu de `www/core/` :

```
Failed to open stream: /home/spheree/vendor/composer/../../web/core/includes/bootstrap.inc
```

Cela se produit car l'autoloader a été généré avec des chemins pointant vers `web/`, mais sur OVH le dossier s'appelle `www/`.

## ✅ Solution 1 : Régénérer le dossier de déploiement (RECOMMANDÉ)

Le script `prepare-ovh-deploy.sh` a été mis à jour pour corriger automatiquement ce problème.

1. **Exécutez le script mis à jour :**
   ```bash
   ./prepare-ovh-deploy.sh
   ```

2. **Ré-uploader sur OVH :**
   - Supprimez tout sur le FTP OVH
   - Uploader tout le contenu de `deploy-ovh/` à nouveau

## ✅ Solution 2 : Correction manuelle sur le serveur

Si vous ne pouvez pas régénérer localement, vous pouvez corriger directement sur OVH :

### Option A : Via SSH (si disponible)

```bash
cd /home/spheree
php fix-autoloader.php
```

Puis supprimez le fichier :
```bash
rm fix-autoloader.php
```

### Option B : Via FTP

1. **Uploadez le fichier `fix-autoloader.php`** à la racine de votre FTP (même niveau que `www/` et `vendor/`)

2. **Accédez à :** `https://www.spherevoices.com/fix-autoloader.php`

3. **Supprimez le fichier** immédiatement après

### Option C : Correction manuelle des fichiers

Si vous avez accès SSH, corrigez manuellement :

```bash
cd /home/spheree/vendor/composer
find . -type f -name "*.php" -exec sed -i 's|../../web/|../../www/|g' {} \;
find . -type f -name "*.php" -exec sed -i 's|/web/|/www/|g' {} \;
```

## ⚠️ Important : Configuration de la base de données

Le diagnostic montre aussi que **la base de données n'est pas configurée**. 

Le fichier `settings.php` charge automatiquement les variables depuis `.env.production` quand vous êtes sur `www.spherevoices.com`.

### Configuration via .env.production (RECOMMANDÉ)

1. **Localement, avant l'upload :**
   - Ouvrez `deploy-ovh/.env.production`
   - Remplissez avec vos informations OVH :
   ```env
   DB_DRIVER=mysql
   DB_HOST=localhost
   DB_PORT=3306
   DB_NAME=votre_nom_base_ovh
   DB_USER=votre_utilisateur_ovh
   DB_PASSWORD=votre_mot_de_passe_ovh
   DB_PREFIX=
   DB_COLLATION=utf8mb4_general_ci
   ```

2. **Ou directement sur le FTP OVH :**
   - Uploadez `.env.production` à la racine (même niveau que `www/` et `vendor/`)
   - Modifiez-le avec vos informations OVH

   **Où trouver ces informations :**
   - Espace client OVH → Hébergement → Bases de données
   - Ou dans les emails de création de base de données

### Alternative : Modification directe de settings.php

Si vous préférez modifier directement `settings.php`, trouvez la section vers la ligne 980 et remplacez les valeurs par défaut.

## 📋 Checklist complète

- [ ] Régénérer `deploy-ovh/` avec le script mis à jour
- [ ] Configurer `deploy-ovh/.env.production` avec vos paramètres OVH
- [ ] Ré-uploader tous les fichiers sur OVH (y compris `.env.production` à la racine)
- [ ] Vérifier que `vendor/` est au même niveau que `www/`
- [ ] Vérifier que `.env.production` est à la racine (même niveau que `www/`)
- [ ] Vérifier les permissions sur `sites/default/files/`
- [ ] Tester le site

## 🆘 Si le problème persiste

1. Vérifiez les logs d'erreur PHP sur OVH
2. Utilisez `test-deploy.php` pour un nouveau diagnostic
3. Vérifiez que tous les fichiers ont bien été uploadés (taille des dossiers)

