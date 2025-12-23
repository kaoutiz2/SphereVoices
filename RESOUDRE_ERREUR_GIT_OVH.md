# Résoudre l'erreur Git OVH "local changes would be overwritten"

## 🔴 Problème

```
Error: error: Your local changes to the following files would be overwritten by checkout:
Please commit your changes or stash them before you switch branches.
Aborting
```

## ✅ Solutions

### Solution 1 : Activer le nettoyage dans Git OVH (RECOMMANDÉ)

1. **Espace client OVH** → **Hébergement** → Votre hébergement
2. Allez dans l'onglet **Git**
3. Cliquez sur votre configuration Git
4. Activez l'option **"Nettoyer avant déploiement"** ou **"Clean before deployment"**
5. Sauvegardez
6. Relancez le déploiement

Cette option supprimera automatiquement les fichiers non trackés avant chaque déploiement.

### Solution 2 : Supprimer complètement Git OVH (si vous utilisez GitHub Actions)

Si vous utilisez **GitHub Actions** pour déployer (et non Git OVH), vous pouvez supprimer la configuration Git OVH :

1. **Espace client OVH** → **Hébergement** → **Git**
2. Supprimez la configuration Git OVH
3. Utilisez uniquement GitHub Actions pour déployer

**Avantages :**
- Pas de conflit entre Git OVH et GitHub Actions
- Déploiements plus rapides via GitHub Actions
- Meilleur contrôle sur le processus de déploiement

### Solution 3 : Nettoyer manuellement via FTP

Si vous avez accès FTP :

1. Connectez-vous en FTP
2. Allez dans le dossier où Git OVH déploie (probablement `/www/`)
3. Supprimez les fichiers non trackés qui causent le problème
4. Relancez le déploiement Git OVH

**⚠️ Attention :** Ne supprimez pas les fichiers importants comme `settings.php`, `files/`, etc.

### Solution 4 : Utiliser le script de nettoyage

Si vous avez déjà déployé `www/clean-git.php` :

1. Accédez à : `https://www.spherevoices.com/clean-git.php`
2. Le script vous indiquera quels fichiers doivent être supprimés
3. Supprimez-les via FTP
4. Relancez le déploiement

## 🔍 Identifier les fichiers problématiques

Pour voir quels fichiers causent le problème, connectez-vous en SSH (si disponible) :

```bash
cd /home/spheree/www  # ou le dossier de déploiement Git OVH
git status
```

Cela vous montrera les fichiers modifiés ou non trackés.

## 📋 Recommandation

**Si vous utilisez GitHub Actions :**
- ✅ Supprimez la configuration Git OVH (Solution 2)
- ✅ Utilisez uniquement GitHub Actions pour déployer

**Si vous utilisez Git OVH :**
- ✅ Activez "Nettoyer avant déploiement" (Solution 1)
- ✅ Ou nettoyez manuellement les fichiers (Solution 3)

## ⚠️ Important

Les fichiers suivants ne doivent **JAMAIS** être supprimés :
- `settings.php`
- `sites/default/files/` (dossier des uploads)
- `.env.production`
- `vendor/` (si installé)
