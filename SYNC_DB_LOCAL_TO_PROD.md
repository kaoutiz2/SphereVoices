# SYNCHRONISATION LOCAL → PROD

## Étape 1 : Exporter la base de données locale

```bash
cd /Users/bryangast/Documents/Kaoutiz.dev/SphereVoices/site/www
../vendor/bin/drush sql:dump --result-file=/tmp/drupal-local.sql --gzip
```

## Étape 2 : Importer en production

### Option A : Via script PHP (recommandé)

1. Uploadez `/tmp/drupal-local.sql.gz` sur le serveur
2. Utilisez le script `import-db.php` ci-dessous

### Option B : Via SSH (si vous avez accès)

```bash
# Sur le serveur
gunzip -c drupal-local.sql.gz | mysql -u spheree921 -p spheree921
```

## Étape 3 : Ajuster les URLs et config

Le script `import-db.php` le fera automatiquement.

## Étape 4 : Vider les caches

```
https://www.spherevoices.com/full-reset.php?token=spherevoices2026
```

## ⚠️ IMPORTANT

- Cela va ÉCRASER la base de données de production
- Sauvegardez d'abord si nécessaire
- Vos sessions actuelles seront perdues
- Après import, reconnectez-vous avec Kaoutiz / st?L,.4Q/eYZug@C

## 🎯 RÉSULTAT

Après cette synchronisation, la prod sera IDENTIQUE au local :
- ✅ Formulaire de login avec inputs visibles
- ✅ Toolbar fonctionnelle
- ✅ Galerie affichée correctement
- ✅ Configuration identique

