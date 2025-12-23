# Diagnostic Erreur 500 - Guide Complet

## 🔴 Problème : Erreur 500 même pour les scripts PHP simples

Si même `quick-check.php` génère une erreur 500, le problème est **très fondamental** au niveau de la configuration serveur.

## ✅ Diagnostic Étape par Étape

### Étape 1 : Tester avec un fichier HTML (PAS de PHP)

**Attendez 2-3 minutes** après le déploiement, puis testez :

```
https://www.spherevoices.com/test.html
```

**Résultats possibles :**

- ✅ **Si vous voyez "Le serveur web fonctionne !"** :
  - Le serveur web fonctionne
  - Le DocumentRoot est correct
  - Le problème vient de PHP uniquement
  - → Passez à l'Étape 2

- ❌ **Si vous avez encore une erreur 500** :
  - Le DocumentRoot est incorrect
  - OU le serveur web ne fonctionne pas
  - → Passez à l'Étape 3

### Étape 2 : Si le HTML fonctionne (problème PHP uniquement)

**Vérifications dans OVH :**

1. **Espace client OVH** → **Hébergement** → **Multisite**
2. Cliquez sur `www.spherevoices.com`
3. Vérifiez :
   - **Activer PHP** : Doit être **Oui** ✅
   - **Version PHP** : Doit être **8.1** ou **8.2** ✅
4. Si ce n'est pas le cas, modifiez et attendez 2-3 minutes

**Vérifications FTP :**

1. Connectez-vous en FTP
2. Vérifiez que `www/index.php` existe
3. Vérifiez les permissions : `www/index.php` doit être **644** ou **755**

### Étape 3 : Si même le HTML ne fonctionne pas (DocumentRoot incorrect)

**Vérifications dans OVH Multisite :**

1. **Espace client OVH** → **Hébergement** → **Multisite**
2. Cliquez sur `www.spherevoices.com`
3. Vérifiez le **Dossier racine** :
   - Doit être : `www` (sans slash)
   - OU : `/www` (avec slash)
   - Ne doit PAS être : `/www/www` ou vide

**Vérifications FTP :**

1. Connectez-vous en FTP
2. Allez dans le répertoire racine (généralement `/home/spheree/` ou similaire)
3. Vérifiez la structure :
   ```
   /home/spheree/  (racine FTP)
   ├── www/              ← Vos fichiers doivent être ICI
   │   ├── index.php
   │   ├── test.html
   │   ├── quick-check.php
   │   └── ...
   ├── vendor/
   └── config/
   ```

4. **Si vos fichiers sont dans `/www/www/` au lieu de `/www/`** :
   - Soit déplacez les fichiers de `www/www/` vers `www/`
   - Soit changez le DocumentRoot dans OVH vers `/www/www`

### Étape 4 : Vérifier les logs OVH (CRITIQUE)

1. **Espace client OVH** → **Hébergement** → **Logs**
2. Cliquez sur **Logs du serveur web**
3. Cherchez les erreurs **récentes** (dernières heures)
4. **Copiez les erreurs** et partagez-les

**Erreurs communes :**

- `DocumentRoot does not exist` → DocumentRoot pointe vers un dossier inexistant
- `File does not exist` → Fichiers manquants
- `PHP Fatal error` → Problème PHP ou fichier corrompu
- `Permission denied` → Problème de permissions
- `Invalid command` → Problème avec .htaccess

### Étape 5 : Désactiver temporairement .htaccess

Si rien ne fonctionne, testez sans `.htaccess` :

1. Via FTP, **renommez** `www/.htaccess` en `www/.htaccess.bak`
2. Testez : `https://www.spherevoices.com/test.html`
3. Si ça fonctionne, le problème vient de `.htaccess`
4. Remettez `.htaccess` et corrigez-le

## 📋 Checklist de Vérification

- [ ] Testé `https://www.spherevoices.com/test.html` (fichier HTML simple)
- [ ] DocumentRoot vérifié dans OVH Multisite
- [ ] PHP activé et version 8.1+ dans OVH Multisite
- [ ] Structure FTP vérifiée (fichiers dans `/www/` ou `/www/www/`)
- [ ] Logs OVH consultés pour erreurs récentes
- [ ] Permissions vérifiées (644 pour fichiers, 755 pour dossiers)
- [ ] `.htaccess` testé (renommé temporairement)

## 🆘 Informations à Partager

Pour que je puisse vous aider, partagez :

1. **Résultat de `test.html`** : Fonctionne ou erreur 500 ?
2. **DocumentRoot dans OVH** : Quelle valeur exacte ?
3. **PHP activé** : Oui ou Non ?
4. **Version PHP** : Quelle version ?
5. **Structure FTP** : Fichiers dans `/www/` ou `/www/www/` ?
6. **Logs OVH** : Copiez les erreurs récentes

## ⚠️ Solution Rapide : Vérifier le DocumentRoot

**Le problème le plus probable est le DocumentRoot.**

Dans OVH Multisite, le DocumentRoot doit être :
- `www` (sans slash) - si vos fichiers sont dans `/www/` sur FTP
- `/www` (avec slash) - selon la configuration OVH
- **PAS** `/www/www` (sauf si vos fichiers sont vraiment dans `/www/www/`)

**Testez d'abord `test.html`** et dites-moi ce que vous voyez !
