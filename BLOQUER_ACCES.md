# Comment bloquer temporairement l'accès au site

## Méthode 1 : Blocage complet (recommandé pour maintenance)

### Étapes :

1. **Sur votre FTP OVH :**
   - Renommez `www/.htaccess` en `www/.htaccess.bak` (sauvegarde)
   - Uploader `deploy-ovh/www/.htaccess-maintenance-simple`
   - Renommez-le en `.htaccess`

2. **Résultat :**
   - Tous les visiteurs verront une erreur 403
   - Vous pouvez toujours accéder via FTP pour travailler

3. **Pour restaurer :**
   - Supprimez `www/.htaccess` (la version maintenance)
   - Renommez `www/.htaccess.bak` en `www/.htaccess`

## Méthode 2 : Blocage sauf votre IP

### Étapes :

1. **Trouvez votre IP publique :**
   - Allez sur : https://www.whatismyip.com/
   - Notez votre adresse IP (ex: 123.456.789.012)

2. **Modifiez le fichier :**
   - Ouvrez `deploy-ovh/www/.htaccess-maintenance`
   - Remplacez `123\.456\.789\.012` par votre IP (avec des backslashes avant les points)
   - Exemple : Si votre IP est `192.168.1.100`, mettez `192\.168\.1\.100`

3. **Sur votre FTP OVH :**
   - Renommez `www/.htaccess` en `www/.htaccess.bak`
   - Uploader le fichier modifié et renommez-le en `.htaccess`

4. **Résultat :**
   - Seule votre IP peut accéder au site
   - Tous les autres visiteurs verront une erreur 403

## Méthode 3 : Page de maintenance personnalisée

### Étapes :

1. **Uploader la page de maintenance :**
   - Uploader `deploy-ovh/www/maintenance.html` dans `www/` sur votre FTP

2. **Modifier .htaccess :**
   - Utilisez la méthode 1 ou 2, mais décommentez la section "Alternative" dans `.htaccess-maintenance`
   - Les visiteurs verront une belle page de maintenance au lieu d'une erreur 403

## ⚠️ Important

- **N'oubliez pas de restaurer** le `.htaccess` original après vos modifications !
- **Testez d'abord** avec la méthode 2 (votre IP) pour vous assurer que vous pouvez toujours accéder
- **Sauvegardez toujours** votre `.htaccess` original avant de le modifier

## Fichiers disponibles

- `deploy-ovh/www/.htaccess-maintenance` - Blocage sauf votre IP
- `deploy-ovh/www/.htaccess-maintenance-simple` - Blocage complet
- `deploy-ovh/www/maintenance.html` - Page de maintenance personnalisée
