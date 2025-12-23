# Supprimer complètement Git OVH

## 🔴 Problème

OVH bloque la modification du DocumentRoot même si Git est "Désactivé" pour le domaine. Il peut y avoir une configuration Git au niveau de l'hébergement.

## ✅ Solution : Supprimer TOUTE configuration Git OVH

### Étape 1 : Vérifier la configuration Git au niveau de l'hébergement

1. **Espace client OVH** → **Hébergement** → Votre hébergement
2. Allez dans l'onglet **Git** (pas Multisite)
3. Vérifiez s'il y a une configuration Git listée ici
4. Si oui, notez le **Dossier de déploiement** (probablement `/www`)

### Étape 2 : Supprimer la configuration Git

1. Dans l'onglet **Git**, cliquez sur la configuration Git existante
2. Cliquez sur **Supprimer** ou **Désactiver**
3. Confirmez la suppression
4. **Attendez 2-3 minutes** que la suppression prenne effet

### Étape 3 : Vérifier qu'il n'y a plus de configuration Git

1. Retournez dans **Hébergement** → **Git**
2. Vérifiez qu'il n'y a **aucune configuration Git** listée
3. Si c'est vide, c'est bon ✅

### Étape 4 : Modifier le DocumentRoot

Maintenant que Git est complètement supprimé :

1. **Espace client OVH** → **Hébergement** → **Multisite**
2. Cliquez sur `www.spherevoices.com`
3. Cliquez sur **Modifier**
4. Changez le **Dossier racine** de `/www/www` vers `/www`
5. **Activer PHP** : Oui
6. **Version PHP** : 8.1 ou 8.2
7. Cliquez sur **Valider**

### Étape 5 : Tester

1. **Attendez 2-3 minutes** que les changements prennent effet
2. Testez : `https://www.spherevoices.com/simple-test.php`
3. Vous devriez voir : "PHP fonctionne! Version: 8.x.x"

## 🔍 Si vous ne trouvez pas l'onglet Git

Si vous ne voyez pas l'onglet **Git** dans votre hébergement :

1. Vérifiez que vous avez bien un hébergement OVH (pas juste un domaine)
2. Certains hébergements n'ont pas Git activé par défaut
3. Dans ce cas, contactez le support OVH pour supprimer toute trace de Git

## ⚠️ Alternative : Si vous ne pouvez vraiment pas supprimer Git

Si après toutes ces étapes, OVH bloque toujours la modification :

**Option A : Créer un lien symbolique (via SSH)**

Si vous avez accès SSH :
```bash
cd /home/spheree/www
ln -s . www
```

Cela créera un lien `www/www` qui pointe vers `www/`, permettant au DocumentRoot `/www/www` de fonctionner.

**Option B : Déplacer temporairement les fichiers**

1. Via FTP, créez un dossier `www/www/`
2. Déplacez temporairement les fichiers de `www/` vers `www/www/`
3. Changez le DocumentRoot vers `/www/www`
4. Testez
5. Si ça fonctionne, gardez cette structure OU déplacez les fichiers de retour vers `www/` et créez un lien symbolique

## 📋 Checklist

- [ ] Onglet Git vérifié dans Hébergement
- [ ] Configuration Git supprimée (s'il y en avait une)
- [ ] Attendu 2-3 minutes après suppression
- [ ] DocumentRoot modifié de `/www/www` vers `/www`
- [ ] PHP activé et version 8.1+
- [ ] Attendu 2-3 minutes après modification DocumentRoot
- [ ] Testé `https://www.spherevoices.com/simple-test.php`
