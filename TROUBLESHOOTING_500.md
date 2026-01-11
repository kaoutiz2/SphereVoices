# Dépannage Erreur HTTP 500 - OVH

## 🔍 Diagnostic rapide

### Étape 1 : Utiliser le script de diagnostic

1. Le fichier `test-deploy.php` a été créé dans `deploy-ovh/www/`
2. Uploadez-le sur votre FTP OVH dans `www/`
3. Accédez à : `https://www.spherevoices.com/test-deploy.php`
4. Le script vous indiquera les problèmes détectés
5. **SUPPRIMEZ ce fichier après diagnostic !**

### Étape 2 : Vérifier les causes les plus courantes

## ❌ Causes courantes de l'erreur 500

### 1. Base de données non configurée

**Symptôme :** `$databases` est vide dans `settings.php`

**Solution :**
1. Connectez-vous à votre FTP OVH
2. Ouvrez `www/sites/default/settings.php`
3. Configurez la base de données :

```php
$databases['default']['default'] = [
  'database' => 'votre_nom_base',
  'username' => 'votre_utilisateur',
  'password' => 'votre_mot_de_passe',
  'host' => 'localhost', // ou l'host fourni par OVH
  'port' => '3306',
  'driver' => 'mysql',
  'prefix' => '',
  'collation' => 'utf8mb4_general_ci',
];
```

**Où trouver ces informations :**
- Dans votre espace client OVH → Hébergement → Bases de données
- Ou dans les emails de création de base de données

### 2. Dossier vendor/ mal placé

**Symptôme :** Erreur "Class not found" ou "autoload.php not found"

**Vérification :**
- Le dossier `vendor/` doit être **au même niveau** que `www/`, pas dedans
- Structure correcte :
  ```
  FTP/
  ├── www/
  └── vendor/    ← Au même niveau que www/
  ```

**Solution :**
1. Vérifiez la structure sur votre FTP
2. Si `vendor/` est dans `www/`, déplacez-le au niveau parent
3. Vérifiez que `www/autoload.php` existe et contient : `require __DIR__ . '/../vendor/autoload.php';`

### 3. Permissions incorrectes

**Symptôme :** Erreurs d'écriture ou de lecture

**Solution :**
Sur votre FTP OVH, vérifiez les permissions :
- `www/sites/default/files/` → 755 ou 777
- `www/sites/default/settings.php` → 644

**Comment modifier les permissions :**
- Via FileZilla : Clic droit → Propriétés du fichier → Permissions
- Via SSH (si disponible) : `chmod 755 www/sites/default/files`

### 4. Version PHP incompatible

**Symptôme :** Erreurs de syntaxe ou classes non trouvées

**Solution :**
1. Vérifiez la version PHP dans votre espace client OVH
2. Drupal 10 nécessite PHP 8.1+
3. Changez la version PHP dans OVH si nécessaire :
   - Espace client OVH → Hébergement → Configuration → Version PHP

### 5. Extensions PHP manquantes

**Extensions requises :**
- `pdo`
- `pdo_mysql`
- `mbstring`
- `xml`
- `gd`
- `json`
- `curl`

**Solution :**
Activez ces extensions dans votre espace client OVH ou via un fichier `.htaccess` ou `php.ini`

### 6. Fichiers manquants ou corrompus

**Vérification :**
- `www/index.php` existe
- `www/.htaccess` existe
- `www/core/` existe
- `www/autoload.php` existe

**Solution :**
Ré-uploader les fichiers manquants depuis `deploy-ovh/`

## 📋 Checklist de vérification

- [ ] Base de données configurée dans `settings.php`
- [ ] Dossier `vendor/` au même niveau que `www/` (pas dedans)
- [ ] Permissions correctes sur `sites/default/files/`
- [ ] PHP 8.1+ activé sur OVH
- [ ] Toutes les extensions PHP nécessaires activées
- [ ] Tous les fichiers uploadés (vérifier la taille des dossiers)
- [ ] Base de données créée et importée (si migration)

## 🔧 Accès aux logs d'erreur OVH

Pour voir les erreurs détaillées :

1. **Via l'espace client OVH :**
   - Hébergement → Logs → Logs du serveur web
   - Ou : Statistiques et logs → Logs

2. **Via FTP :**
   - Cherchez un dossier `logs/` à la racine
   - Ou dans `www/sites/default/files/` (si activé)

3. **Activer l'affichage des erreurs temporairement :**
   Ajoutez au début de `www/index.php` (temporairement) :
   ```php
   error_reporting(E_ALL);
   ini_set('display_errors', 1);
   ```
   **⚠️ Retirez ces lignes après diagnostic !**

## 🆘 Si rien ne fonctionne

1. **Vérifiez les logs d'erreur PHP** sur OVH
2. **Contactez le support OVH** avec :
   - L'URL du site
   - Les logs d'erreur
   - La version PHP utilisée
3. **Vérifiez que la base de données est bien créée** et accessible
4. **Testez avec le script de diagnostic** (`test-deploy.php`)

## 📝 Notes importantes

- Le fichier `test-deploy.php` doit être **supprimé** après diagnostic
- Ne laissez jamais `display_errors` activé en production
- Vérifiez toujours que `vendor/` est au bon endroit (même niveau que `www/`)
- Les permissions doivent être sécurisées (644 pour les fichiers, 755 pour les dossiers)



