# Changelog - Restructuration pour OVH Git

## Date : 14 décembre 2025

## Changements effectués

### 1. Renommage de `web/` en `www/`
- Le dossier `web/` a été renommé en `www/` pour correspondre à la structure OVH
- Tous les fichiers Drupal sont maintenant dans `www/`

### 2. Mise à jour de `composer.json`
- Toutes les références à `web/` ont été remplacées par `www/`
- Les chemins d'installation pointent maintenant vers `www/`

### 3. Mise à jour de `.gitignore`
- Les chemins ont été mis à jour de `web/` vers `www/`
- Le dossier `deploy-ovh/` a été retiré (plus nécessaire)

### 4. Suppression des fichiers de déploiement
- `deploy-ovh/` supprimé
- `prepare-ovh-deploy.sh` supprimé
- Plus besoin de script de préparation, la structure est directement prête

### 5. Mise à jour de la documentation
- `README.md` mis à jour
- `INSTALLATION.md` mis à jour
- `DOCUMENTATION.md` mis à jour
- `QUICK_START.md` mis à jour
- `install.sh` mis à jour
- `DEPLOIEMENT_OVH.md` réécrit pour Git

### 6. Régénération de l'autoloader
- L'autoloader a été régénéré avec les bons chemins (`www/`)

## Structure finale

```
site/
├── www/              # Racine web (DocumentRoot) - Prêt pour OVH
├── vendor/          # Dépendances Composer
├── config/          # Configuration Drupal
├── composer.json
└── ...
```

## Pour OVH Git

La structure est maintenant directement compatible avec OVH :
- OVH peut pointer vers le repo Git
- Le dossier `www/` sera directement utilisé comme DocumentRoot
- `vendor/` et `config/` sont au bon endroit

## Action requise

1. **Créer `.env.production`** sur OVH après le premier déploiement Git
2. **Configurer les permissions** sur `www/sites/default/files/`
3. **Vider le cache** après le premier déploiement

## Notes

- L'autoloader pointe vers `../vendor/autoload.php` (correct pour la structure OVH)
- `settings.php` utilise `$app_root` qui sera automatiquement `www/`
- Tous les chemins sont maintenant cohérents avec la structure OVH

