# Corriger le DocumentRoot OVH

## 🔴 Problème actuel

- DocumentRoot configuré : `/www/www`
- DocumentRoot nécessaire : `/www`
- OVH bloque la modification car Git est configuré

## ✅ Solution : Supprimer Git OVH puis corriger DocumentRoot

Puisque vous utilisez maintenant **GitHub Actions** pour déployer (et non Git OVH), vous pouvez supprimer la configuration Git OVH.

### Étape 1 : Supprimer la configuration Git OVH

1. **Espace client OVH** → **Hébergement** → Votre hébergement
2. Allez dans l'onglet **Git**
3. Si une configuration Git existe, **supprimez-la** :
   - Cliquez sur la configuration Git
   - Cliquez sur **Supprimer** ou **Désactiver**
   - Confirmez la suppression

**⚠️ Important :** Cela ne supprimera PAS vos fichiers, seulement la configuration Git OVH.

### Étape 2 : Corriger le DocumentRoot

1. **Espace client OVH** → **Hébergement** → **Multisite**
2. Cliquez sur `www.spherevoices.com`
3. Cliquez sur **Modifier**
4. Changez le **Dossier racine** de `/www/www` vers `/www`
5. Vérifiez que :
   - **Activer PHP** : Oui
   - **Version PHP** : 8.1 ou 8.2
6. Cliquez sur **Valider**

### Étape 3 : Attendre et tester

1. **Attendez 2-3 minutes** que les changements prennent effet
2. Testez : `https://www.spherevoices.com/simple-test.php`
3. Vous devriez voir : "PHP fonctionne! Version: 8.x.x"

### Étape 4 : Vérifier la structure FTP

Connectez-vous en FTP et vérifiez que vos fichiers sont bien dans `/www/` :

```
/home/spheree/  (racine FTP)
├── www/              ← DocumentRoot doit pointer ici
│   ├── index.php
│   ├── simple-test.php
│   ├── core/
│   ├── modules/
│   └── ...
├── vendor/
├── config/
└── .env.production
```

**Si vous voyez `www/www/` :**
- C'est normal si Git OVH avait créé cette structure
- Après avoir changé le DocumentRoot vers `/www`, ça devrait fonctionner
- Si ça ne fonctionne pas, il faudra peut-être déplacer les fichiers de `www/www/` vers `www/`

## 🔍 Vérification

Après avoir corrigé le DocumentRoot, testez dans cet ordre :

1. `https://www.spherevoices.com/simple-test.php` → Doit afficher "PHP fonctionne!"
2. `https://www.spherevoices.com/check-500.php` → Diagnostic complet
3. `https://www.spherevoices.com/install-ovh.php` → Installation Drupal
4. `https://www.spherevoices.com` → Site Drupal

## ⚠️ Si vous avez encore des problèmes

Si après avoir changé le DocumentRoot vers `/www`, vous avez toujours une erreur 500 :

1. **Vérifiez la structure FTP** :
   - Les fichiers sont-ils dans `/www/` ou `/www/www/` ?
   - Si dans `/www/www/`, vous devrez soit :
     - Déplacer les fichiers de `www/www/` vers `www/` (via FTP)
     - OU remettre le DocumentRoot à `/www/www`

2. **Vérifiez les logs OVH** :
   - Hébergement → Logs → Logs du serveur web
   - Cherchez les erreurs récentes
