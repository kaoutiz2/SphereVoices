# ⚠️ ATTENTION : Vidage du cache requis après déploiement

## 🚀 Le déploiement est terminé avec succès !

Cependant, **le cache Drupal doit être vidé manuellement** pour que les changements soient visibles sur le site en production.

---

## 🔧 Comment vider le cache ?

### ✅ Méthode 1 : Via navigateur (LA PLUS SIMPLE)

Cliquez sur ce lien ou copiez-le dans votre navigateur :

```
https://www.spherevoices.com/www/clear-cache-web.php?token=spherevoices2026
```

**Avantages :**
- ✅ Aucun accès SSH requis
- ✅ Interface visuelle
- ✅ Confirmation immédiate du succès
- ✅ Fonctionne depuis n'importe où

---

### ✅ Méthode 2 : Via l'interface d'administration Drupal

1. Connectez-vous en tant qu'administrateur sur : https://www.spherevoices.com/user/login
2. Allez sur : **Configuration** > **Development** > **Performance**
3. Cliquez sur le bouton : **"Clear all caches"**

**Avantages :**
- ✅ Méthode officielle Drupal
- ✅ Aucun fichier supplémentaire requis

---

### ✅ Méthode 3 : Via SSH (si vous avez un accès)

```bash
# Se connecter en SSH
ssh votre_user@ssh.clusterXXX.hosting.ovh.net

# Se placer dans le bon répertoire
cd ~/

# Option A : Utiliser le script dédié
php post-deploy.php

# Option B : Utiliser Drush
vendor/bin/drush cr

# Option C : Utiliser le script shell
./clear-cache.sh
```

---

## 🤔 Pourquoi le cache ne se vide-t-il pas automatiquement ?

Les hébergements mutualisés OVH ont des **limitations** :
- ❌ Pas d'accès root (pas de `sudo`)
- ❌ Pas de commandes système (pas de `apt-get`)
- ❌ SSH parfois désactivé ou limité
- ❌ Pas de webhooks ou cron automatiques après FTP

**Solution :** Vidage manuel après chaque déploiement (30 secondes) 🎯

---

## 📝 Après avoir vidé le cache

1. ✅ Actualisez votre navigateur : **Ctrl + Shift + R** (Windows/Linux) ou **Cmd + Shift + R** (Mac)
2. ✅ Vérifiez que les changements sont visibles
3. ✅ Si besoin, videz aussi le cache de votre navigateur

---

## 🔒 Sécurité

Le fichier `clear-cache-web.php` est protégé par un token. 

**Pour plus de sécurité :**
1. Changez le token dans le fichier
2. Ou supprimez le fichier après utilisation
3. Ou ajoutez une protection `.htaccess`

---

## 🆘 En cas de problème

Si le vidage de cache échoue :

1. **Vérifiez les permissions** des fichiers
2. **Consultez les logs Drupal** : Reports > Recent log messages
3. **Tentez plusieurs méthodes** (navigateur, interface, SSH)
4. **Contactez le support OVH** si SSH ne fonctionne pas

---

## 📚 Documentation complète

Pour plus d'informations, consultez : `CACHE_MANAGEMENT.md`

---

🎉 **Une fois le cache vidé, votre site affichera la dernière version !**

