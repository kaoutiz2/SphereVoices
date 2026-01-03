# Gestion du Cache en Production

Ce document explique le système de gestion automatique du cache pour éviter les problèmes de cache en production.

## 🔧 Problème résolu

En production, Drupal peut garder en cache les anciennes versions du CSS, JS et des templates. Ce système force le vidage automatique du cache après chaque déploiement.

## 📦 Composants du système

### 1. Script de post-déploiement (`post-deploy.php`)

Script PHP qui s'exécute automatiquement après chaque déploiement pour :
- ✅ Vider tous les caches Drupal
- ✅ Invalider les caches CSS/JS
- ✅ Reconstruire le registre des routes
- ✅ Invalider les caches de rendu

**Utilisation manuelle** :
```bash
php post-deploy.php
```

### 2. Workflow GitHub Actions (`.github/workflows/deploy-ovh.yml`)

Le workflow de déploiement inclut maintenant une étape finale (Step 6/6) qui :
1. Se connecte en SSH au serveur OVH
2. Exécute le script `post-deploy.php`
3. En cas d'échec, tente avec `drush cr`
4. En dernier recours, vide manuellement les caches via PHP

### 3. Deployment Identifier (`www/sites/default/settings.php`)

Un identifiant de déploiement dynamique basé sur :
- La version de Drupal
- Le timestamp du fichier `spherevoices_theme.info.yml`

Cet identifiant change à chaque déploiement, forçant l'invalidation du conteneur de services.

```php
$settings['deployment_identifier'] = \Drupal::VERSION . '.' . filemtime(...);
```

### 4. Headers HTTP anti-cache (`.htaccess`)

Un fichier `.htaccess` dans le thème qui :
- Désactive le cache navigateur pour les CSS et JS
- Force le rechargement des assets

## 🚀 Déploiement automatique

À chaque push sur `production`, le workflow :
1. **Déploie les fichiers** via FTP
2. **Upload le script** `post-deploy.php`
3. **Se connecte en SSH** et exécute le script
4. **Vide le cache** automatiquement

## 🔑 Secrets GitHub requis

Pour que le vidage de cache fonctionne, vous devez configurer ces secrets dans GitHub :

- `OVH_FTP_HOST` : Hôte FTP
- `OVH_FTP_USER` : Utilisateur FTP
- `OVH_FTP_PASSWORD` : Mot de passe FTP
- `OVH_SSH_HOST` : Hôte SSH (ex: ssh.cluster027.hosting.ovh.net)
- `OVH_SSH_USER` : Utilisateur SSH
- `OVH_SSH_PASSWORD` : Mot de passe SSH

### Comment ajouter les secrets SSH :

1. Allez sur GitHub : **Settings** > **Secrets and variables** > **Actions**
2. Cliquez sur **New repository secret**
3. Ajoutez :
   - Name: `OVH_SSH_HOST`
   - Value: `ssh.clusterXXX.hosting.ovh.net` (votre cluster OVH)
4. Répétez pour `OVH_SSH_USER` et `OVH_SSH_PASSWORD`

## 🛠️ Commandes manuelles

Si vous devez vider le cache manuellement en production :

### Via SSH :
```bash
# Se connecter en SSH
ssh votre_user@ssh.clusterXXX.hosting.ovh.net

# Vider le cache avec le script
php post-deploy.php

# OU avec drush
vendor/bin/drush cr
```

### Via l'interface Drupal :
1. Connectez-vous en tant qu'admin
2. Allez sur : **Configuration** > **Development** > **Performance**
3. Cliquez sur **Clear all caches**

## 🧪 Test du système

Pour tester que le système fonctionne :

1. Modifiez un fichier CSS dans le thème
2. Committez et push sur `production`
3. Le workflow GitHub Actions devrait :
   - ✅ Déployer les fichiers
   - ✅ Exécuter le post-deploy
   - ✅ Afficher "Cache vidé avec succès"
4. Actualisez le site en production (Ctrl+Shift+R)
5. Les changements doivent être visibles immédiatement

## 📊 Monitoring

Vérifiez les logs du workflow GitHub Actions :
- Allez sur l'onglet **Actions** de votre repo
- Cliquez sur le dernier workflow
- Consultez l'étape **"Clear Drupal cache via SSH (Step 6/6)"**
- Vous devriez voir : `✅ Cache vidé avec post-deploy.php`

## 🔧 Dépannage

### Le cache n'est toujours pas vidé

1. Vérifiez que les secrets SSH sont bien configurés
2. Testez la connexion SSH manuellement
3. Vérifiez les logs du workflow GitHub Actions
4. Essayez de vider le cache manuellement via SSH

### Erreur de connexion SSH

Si l'étape SSH échoue :
```bash
# Testez la connexion SSH localement
ssh votre_user@ssh.clusterXXX.hosting.ovh.net

# Vérifiez que vous êtes dans le bon répertoire
pwd
# Devrait afficher: /homez.XXX/votre_user

# Vérifiez que le script existe
ls -la post-deploy.php
```

### Le script post-deploy.php ne s'exécute pas

Vérifiez les permissions :
```bash
chmod +x post-deploy.php
php post-deploy.php
```

## 🎯 Alternative : Cache manuel systématique

Si le système automatique ne fonctionne pas, vous pouvez :

1. **Créer un cron** qui vide le cache toutes les 5 minutes
2. **Utiliser un webhook** depuis GitHub vers un endpoint qui vide le cache
3. **Créer un module custom** qui vide le cache à chaque requête (NON RECOMMANDÉ en prod)

## 📝 Notes importantes

- ⚠️ Le vidage de cache peut prendre 30 secondes à 1 minute
- ⚠️ Le site peut être légèrement ralenti juste après le déploiement (temps de reconstruction du cache)
- ✅ Le cache se reconstruit automatiquement au fur et à mesure des visites
- ✅ Ce système garantit que les visiteurs voient toujours la dernière version du site

## 🔗 Fichiers concernés

- `post-deploy.php` : Script de vidage de cache
- `.github/workflows/deploy-ovh.yml` : Workflow de déploiement
- `www/sites/default/settings.php` : Configuration du deployment_identifier
- `www/themes/custom/spherevoices_theme/.htaccess` : Headers anti-cache

## 📧 Support

En cas de problème, vérifiez :
1. Les logs GitHub Actions
2. Les logs SSH d'OVH
3. Les logs Drupal (Reports > Recent log messages)

