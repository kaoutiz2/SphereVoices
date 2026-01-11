# Dépannage Final - Erreur 500

## Situation actuelle

✅ Drupal se charge correctement (kernel boot réussi)
✅ Base de données connectée
❌ Erreur 500 lors du traitement de la requête

## Méthodes pour trouver l'erreur exacte

### Méthode 1 : Vérifier les logs OVH (RECOMMANDÉ)

1. **Connectez-vous à votre espace client OVH**
2. **Allez dans :** Hébergement → Logs → **Logs du serveur web**
3. **Cherchez les erreurs récentes** (dernières heures)
4. **Recherchez :** "PHP", "Fatal", "Error", "Exception"
5. **Copiez l'erreur complète** avec le stack trace

### Méthode 2 : Utiliser le script check-logs.php

1. **Uploader** `deploy-ovh/www/check-logs.php` dans `www/` sur votre FTP
2. **Accéder à :** `https://www.spherevoices.com/check-logs.php`
3. Le script cherchera les fichiers de log et affichera les dernières erreurs

### Méthode 3 : Vérifier manuellement les logs sur le FTP

Sur votre FTP OVH, cherchez ces fichiers/dossiers :
- `/logs/error_log`
- `/error_log`
- `/www/error_log`
- `/logs/`

### Méthode 4 : Activer les logs dans settings.php

Ajoutez temporairement dans `settings.php` (après la ligne 91) :

```php
// TEMPORAIRE - À supprimer après diagnostic
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../error_log.txt');
```

Puis créez un fichier `error_log.txt` à la racine (même niveau que `www/`) avec permissions 666.

## Causes probables d'une erreur 500 après boot réussi

1. **Problème avec un module spécifique**
   - Un module personnalisé ou contrib cause une erreur
   - Solution : Désactiver les modules un par un

2. **Problème de cache corrompu**
   - Le cache Drupal est corrompu
   - Solution : Vider le cache manuellement (supprimer `www/sites/default/files/php/twig` et autres dossiers de cache)

3. **Problème de permissions**
   - Certains fichiers/dossiers n'ont pas les bonnes permissions
   - Solution : Vérifier les permissions sur `sites/default/files/`

4. **Problème avec la configuration**
   - Une configuration Drupal est corrompue
   - Solution : Vérifier la base de données ou réimporter la configuration

5. **Problème avec un hook ou un service**
   - Un hook_* ou un service personnalisé cause une erreur
   - Solution : Vérifier les modules personnalisés

## Actions immédiates

1. **Vérifier les logs OVH** (Méthode 1) - C'est la plus rapide
2. **Partager l'erreur exacte** trouvée dans les logs
3. **On corrigera le problème** une fois qu'on aura l'erreur exacte

## Si vous ne trouvez pas les logs

Essayez d'accéder à une page spécifique qui pourrait donner plus d'infos :
- `https://www.spherevoices.com/user/login`
- `https://www.spherevoices.com/admin`
- `https://www.spherevoices.com/node`

Parfois certaines pages fonctionnent et d'autres non, ce qui peut donner des indices.



