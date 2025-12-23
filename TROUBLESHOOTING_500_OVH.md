# Résolution Erreur 500 - Configuration OVH

## 🔴 Problème : Internal Server Error (500)

Si vous obtenez une erreur 500 même pour des scripts PHP simples, c'est un problème de configuration serveur OVH.

## ✅ Vérifications à faire dans OVH

### 1. Vérifier le DocumentRoot (CRITIQUE)

1. **Espace client OVH** → **Hébergement** → Votre hébergement
2. Allez dans l'onglet **Multisite**
3. Cliquez sur `www.spherevoices.com` (ou votre domaine)
4. Vérifiez le **Dossier racine** :
   - ✅ Doit être : `/www` (si vos fichiers sont dans `/www/`)
   - ❌ Ne doit PAS être : `/` ou `/www/www` ou vide

**Si le DocumentRoot est incorrect :**
- Cliquez sur **Modifier**
- Changez le **Dossier racine** vers `/www`
- **Activer PHP** : Oui
- **Version PHP** : 8.1 ou 8.2
- Sauvegardez

### 2. Vérifier que PHP est activé

Dans **Multisite** → Votre domaine :
- **Activer PHP** : Doit être **Oui**
- **Version PHP** : Doit être **8.1** ou **8.2** (pas 5.4, 7.x, etc.)

### 3. Vérifier la structure des fichiers sur FTP

Connectez-vous en FTP et vérifiez la structure :

```
/home/spheree/  (ou votre répertoire FTP)
├── www/              ← Le DocumentRoot doit pointer ici
│   ├── index.php
│   ├── test.php
│   ├── simple-test.php
│   ├── .htaccess
│   ├── core/
│   ├── modules/
│   └── ...
├── vendor/
├── config/
└── .env.production
```

**Si vous voyez `www/www/` au lieu de `www/` :**
- Le DocumentRoot doit pointer vers `/www/www` (pas `/www`)

### 4. Tester avec un fichier PHP simple

Après avoir corrigé le DocumentRoot, testez :

1. **Attendez 2-3 minutes** que les changements prennent effet
2. Accédez à : `https://www.spherevoices.com/simple-test.php`
3. Vous devriez voir : "PHP fonctionne! Version: 8.x.x"

**Si ça ne fonctionne toujours pas :**
- Vérifiez les logs OVH : **Hébergement** → **Logs** → **Logs du serveur web**
- Cherchez les erreurs récentes

### 5. Vérifier les permissions

Via FTP, vérifiez les permissions :
- `www/index.php` : doit être **644** ou **755**
- `www/` : doit être **755**
- `www/sites/default/files/` : doit être **777** (si existe)

### 6. Désactiver temporairement .htaccess

Si rien ne fonctionne, renommez temporairement `.htaccess` :

Via FTP :
1. Renommez `www/.htaccess` en `www/.htaccess.bak`
2. Testez `https://www.spherevoices.com/simple-test.php`
3. Si ça fonctionne, le problème vient de `.htaccess`
4. Remettez `.htaccess` et corrigez-le

## 🔍 Diagnostic via les logs OVH

1. **Espace client OVH** → **Hébergement** → **Logs**
2. Cliquez sur **Logs du serveur web**
3. Cherchez les erreurs récentes (dernières 24h)
4. Les erreurs communes :
   - `DocumentRoot does not exist` → DocumentRoot incorrect
   - `PHP Fatal error` → Problème PHP
   - `Permission denied` → Problème de permissions
   - `File does not exist` → Fichiers manquants

## ✅ Checklist de vérification

- [ ] DocumentRoot = `/www` (ou `/www/www` selon votre structure)
- [ ] PHP activé = **Oui** dans Multisite
- [ ] Version PHP = **8.1** ou **8.2**
- [ ] Structure FTP correcte (`www/` existe)
- [ ] `www/index.php` existe
- [ ] Permissions correctes (644 pour fichiers, 755 pour dossiers)
- [ ] Logs OVH consultés pour erreurs

## 🆘 Si rien ne fonctionne

1. **Contactez le support OVH** avec :
   - L'URL du site
   - Le message d'erreur exact
   - Les logs d'erreur (copiez depuis OVH)
   - La configuration Multisite (screenshot)

2. **Vérifiez que votre hébergement est actif** :
   - OVH → Hébergement → Votre hébergement
   - Statut doit être "Actif"
