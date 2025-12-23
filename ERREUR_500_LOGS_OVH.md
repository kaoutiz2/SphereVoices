# Diagnostic Erreur 500 - Vérifier les Logs OVH

## 🔴 Problème : Erreur 500 même pour test.html

Si même un fichier HTML simple génère une erreur 500, le problème est **au niveau de la configuration serveur OVH**.

## ✅ Solution : Consulter les Logs OVH (CRITIQUE)

Les logs OVH contiennent l'erreur exacte qui cause le problème.

### Étape 1 : Accéder aux logs OVH

1. **Espace client OVH** → **Hébergement** → Votre hébergement
2. Allez dans l'onglet **Logs**
3. Cliquez sur **Logs du serveur web**
4. Cherchez les erreurs **récentes** (dernières heures)

### Étape 2 : Identifier l'erreur

**Erreurs communes et leurs solutions :**

#### Erreur 1 : `DocumentRoot does not exist`
```
[error] DocumentRoot does not exist: /home/spheree/www
```
**Solution :**
- Le DocumentRoot pointe vers un dossier inexistant
- Vérifiez dans OVH Multisite que le DocumentRoot est correct
- Vérifiez via FTP que le dossier existe

#### Erreur 2 : `Invalid command 'Directory'`
```
[error] Invalid command 'Directory', perhaps misspelled or defined by a module not included in the server configuration
```
**Solution :**
- Le `.htaccess` à la racine contient une directive non supportée
- Renommez temporairement `.htaccess` en `.htaccess.bak` via FTP
- Testez à nouveau

#### Erreur 3 : `Options not allowed here`
```
[error] Options not allowed here
```
**Solution :**
- Problème avec la directive `Options` dans `.htaccess`
- Vérifiez le `.htaccess` à la racine et dans `www/`

#### Erreur 4 : `Permission denied`
```
[error] Permission denied: /home/spheree/www/test.html
```
**Solution :**
- Problème de permissions
- Via FTP, changez les permissions de `www/` à **755**
- Changez les permissions de `www/test.html` à **644**

#### Erreur 5 : `File does not exist`
```
[error] File does not exist: /home/spheree/www/test.html
```
**Solution :**
- Le fichier n'existe pas à l'endroit attendu
- Vérifiez via FTP que `test.html` existe dans `www/`
- Vérifiez que le DocumentRoot pointe vers le bon dossier

### Étape 3 : Partager les logs

**Copiez les erreurs récentes** des logs OVH et partagez-les avec moi. Cela permettra d'identifier le problème exact.

## 🔍 Vérifications Complémentaires

### 1. Vérifier le DocumentRoot dans OVH

1. **Espace client OVH** → **Hébergement** → **Multisite**
2. Cliquez sur `www.spherevoices.com`
3. Notez la valeur exacte du **Dossier racine**
4. Partagez cette valeur

### 2. Vérifier la structure FTP

1. Connectez-vous en FTP
2. Allez dans le répertoire racine
3. Vérifiez où sont vos fichiers :
   - Dans `/www/` ?
   - Dans `/www/www/` ?
   - Ailleurs ?
4. Partagez la structure exacte

### 3. Tester sans .htaccess

1. Via FTP, renommez `www/.htaccess` en `www/.htaccess.bak`
2. Testez : `https://www.spherevoices.com/test.html`
3. Si ça fonctionne, le problème vient de `.htaccess`
4. Remettez `.htaccess` et corrigez-le

### 4. Vérifier les permissions

Via FTP, vérifiez les permissions :
- `www/` : doit être **755**
- `www/test.html` : doit être **644**
- `www/index.php` : doit être **644**

## 📋 Informations à Partager

Pour que je puisse vous aider efficacement, j'ai besoin de :

1. **Les logs OVH** : Copiez les erreurs récentes (dernières heures)
2. **Le DocumentRoot** : Valeur exacte dans OVH Multisite
3. **La structure FTP** : Où sont vos fichiers exactement ?
4. **Résultat du test sans .htaccess** : Fonctionne ou pas ?

## 🆘 Si les logs ne sont pas accessibles

Si vous ne pouvez pas accéder aux logs OVH :

1. **Contactez le support OVH** avec :
   - L'URL : `https://www.spherevoices.com`
   - Le problème : Erreur 500 même pour les fichiers HTML simples
   - La configuration : DocumentRoot = `www` (ou la valeur exacte)
   - Demandez les logs d'erreur récents

2. **Vérifiez que l'hébergement est actif** :
   - OVH → Hébergement → Votre hébergement
   - Statut doit être "Actif"

## ⚠️ Action Immédiate

**La priorité est de consulter les logs OVH.** Ils contiennent l'erreur exacte qui cause le problème.

1. Allez dans **OVH → Hébergement → Logs → Logs du serveur web**
2. Copiez les erreurs récentes (dernières heures)
3. Partagez-les avec moi
