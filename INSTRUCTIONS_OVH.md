# Instructions d'installation OVH - SphereVoices

## 🔴 ÉTAPE 1 : Changer PHP (OBLIGATOIRE)

1. Espace client OVH → **Hébergement** → **Configuration** → **Version PHP**
2. Changez vers **PHP 8.1** ou **PHP 8.2**
3. Attendez 2-3 minutes que le changement prenne effet

**SANS PHP 8.1+, Drupal 10 ne fonctionnera JAMAIS !**

## 🔴 ÉTAPE 2 : Créer .env.production sur le serveur

Via FTP ou gestionnaire de fichiers OVH, créez le fichier `.env.production` à la **racine** (même niveau que `www/`) :

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

## 🔴 ÉTAPE 3 : Exécuter le script d'installation

1. Attendez que Git OVH déploie le fichier `install-ovh.php`
2. Accédez à : `https://www.spherevoices.com/install-ovh.php`
3. Le script va créer automatiquement :
   - `settings.php`
   - Le dossier `files/`
   - Tester la connexion à la base de données

## 🔴 ÉTAPE 4 : Installer vendor/

Le dossier `vendor/` n'est pas dans Git (normal). Vous devez l'installer sur le serveur :

### Option A : Via SSH (recommandé)
```bash
cd /home/spheree
composer install --no-dev --optimize-autoloader
```

### Option B : Via FTP
1. Sur votre machine locale, allez dans le dossier du projet
2. Uploadez tout le dossier `vendor/` à la racine FTP (même niveau que `www/`)

## ✅ Vérifications finales

- [ ] PHP 8.1+ activé dans OVH
- [ ] `.env.production` créé à la racine avec les bons identifiants
- [ ] `settings.php` créé dans `www/sites/default/`
- [ ] Dossier `files/` créé dans `www/sites/default/` avec permissions 777
- [ ] Dossier `vendor/` installé à la racine
- [ ] Script `install-ovh.php` supprimé (sécurité)

## 🧪 Test

Accédez à : `https://www.spherevoices.com`

Si vous voyez encore une erreur 500, vérifiez les logs dans OVH → Hébergement → Logs
