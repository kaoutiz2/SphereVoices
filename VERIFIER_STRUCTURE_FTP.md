# Vérifier la structure FTP et corriger DocumentRoot

## 🔍 Étape 1 : Vérifier où sont vos fichiers sur FTP

Connectez-vous en FTP (FileZilla ou autre) et vérifiez la structure :

### Structure attendue (si GitHub Actions a bien déployé) :

```
/home/spheree/  (ou votre répertoire FTP racine)
├── www/              ← Vos fichiers Drupal devraient être ICI
│   ├── index.php
│   ├── simple-test.php
│   ├── core/
│   ├── modules/
│   └── ...
├── vendor/
├── config/
└── .env.production
```

### Structure si Git OVH avait créé un doublon :

```
/home/spheree/
├── www/
│   └── www/          ← Vos fichiers pourraient être ICI (doublon)
│       ├── index.php
│       ├── core/
│       └── ...
├── vendor/
└── ...
```

## ✅ Solution selon la structure trouvée

### Cas A : Fichiers dans `/www/` (structure correcte)

Si vos fichiers sont directement dans `/www/` :

1. **Espace client OVH** → **Hébergement** → **Multisite**
2. Cliquez sur `www.spherevoices.com`
3. Cliquez sur **Modifier**
4. Changez le **Dossier racine** de `/www/www` vers `/www`
5. **Activer PHP** : Oui
6. **Version PHP** : 8.1 ou 8.2
7. Cliquez sur **Valider**
8. Attendez 2-3 minutes
9. Testez : `https://www.spherevoices.com/simple-test.php`

### Cas B : Fichiers dans `/www/www/` (structure doublon)

Si vos fichiers sont dans `/www/www/` :

**Option 1 : Déplacer les fichiers (RECOMMANDÉ)**

1. Via FTP, déplacez TOUT le contenu de `/www/www/` vers `/www/`
2. Supprimez le dossier vide `/www/www/`
3. Changez le DocumentRoot vers `/www` dans OVH Multisite
4. Testez

**Option 2 : Garder la structure actuelle**

1. Laissez les fichiers dans `/www/www/`
2. Gardez le DocumentRoot à `/www/www` dans OVH
3. Testez : `https://www.spherevoices.com/simple-test.php`

## 🔍 Comment vérifier où sont vos fichiers

1. **Connectez-vous en FTP**
2. Allez dans le répertoire racine (généralement `/home/spheree/` ou similaire)
3. Ouvrez le dossier `www/`
4. Regardez :
   - Si vous voyez directement `index.php`, `core/`, `modules/` → fichiers dans `/www/`
   - Si vous voyez un autre dossier `www/` → fichiers dans `/www/www/`

## ⚠️ Important : Vérifier les logs OVH

Si l'erreur 500 persiste après correction :

1. **Espace client OVH** → **Hébergement** → **Logs**
2. Cliquez sur **Logs du serveur web**
3. Cherchez les erreurs récentes (dernières heures)
4. Les erreurs communes :
   - `DocumentRoot does not exist` → DocumentRoot pointe vers un dossier inexistant
   - `File does not exist` → Fichiers manquants
   - `PHP Fatal error` → Problème PHP ou fichier corrompu

## 📋 Checklist

- [ ] Connecté en FTP
- [ ] Vérifié où sont les fichiers (`/www/` ou `/www/www/`)
- [ ] DocumentRoot corrigé dans OVH Multisite
- [ ] PHP activé et version 8.1+ dans Multisite
- [ ] Attendu 2-3 minutes après modification
- [ ] Testé `https://www.spherevoices.com/simple-test.php`
- [ ] Consulté les logs OVH si erreur persiste
