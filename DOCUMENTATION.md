# Documentation Technique - SphereVoices

## Architecture du Projet

### Structure des Répertoires

```
site/
├── www/                          # Racine web (DocumentRoot) - Prêt pour OVH
│   ├── core/                     # Core Drupal 10
│   ├── modules/
│   │   ├── contrib/              # Modules tiers installés via Composer
│   │   └── custom/               # Modules personnalisés
│   │       └── spherevoices_core/ # Module principal SphereVoices
│   ├── themes/
│   │   ├── contrib/              # Thèmes tiers
│   │   └── custom/               # Thèmes personnalisés
│   │       └── spherevoices_theme/ # Thème principal
│   └── sites/
│       └── default/              # Configuration du site
├── vendor/                       # Dépendances Composer (au même niveau que www/)
├── config/                       # Configuration Drupal (au même niveau que www/)
├── composer.json                 # Dépendances Composer
├── README.md                     # Documentation générale
├── INSTALLATION.md              # Guide d'installation
└── DOCUMENTATION.md             # Ce fichier
```

## Modules Personnalisés

### spherevoices_core

Module principal contenant les fonctionnalités spécifiques à SphereVoices.

#### Fonctionnalités

- **Breaking News API** : Endpoint REST pour récupérer le dernier article marqué comme breaking news
- **Controller** : `BreakingNewsController` pour gérer les requêtes API

#### Fichiers

- `spherevoices_core.info.yml` : Définition du module
- `spherevoices_core.module` : Hooks et fonctions principales
- `spherevoices_core.routing.yml` : Routes API
- `src/Controller/BreakingNewsController.php` : Contrôleur pour l'API Breaking News

#### Utilisation

L'endpoint `/api/breaking-news` retourne un JSON avec les informations du dernier breaking news :

```json
{
  "title": "Titre de l'article",
  "url": "https://example.com/article",
  "summary": "Résumé de l'article"
}
```

## Thème Personnalisé

### spherevoices_theme

Thème personnalisé inspiré du design CNN avec une palette rouge, noir et blanc.

#### Structure

```
spherevoices_theme/
├── spherevoices_theme.info.yml  # Définition du thème
├── spherevoices_theme.libraries.yml # Bibliothèques CSS/JS
├── templates/                    # Templates Twig
│   ├── page.html.twig          # Template de page principal
│   └── node/
│       └── node--article.html.twig # Template pour les articles
├── css/
│   ├── style.css               # Styles principaux
│   ├── components.css          # Composants réutilisables
│   ├── layout.css              # Mise en page
│   └── responsive.css          # Styles responsive
└── js/
    └── main.js                 # JavaScript principal
```

#### Régions Disponibles

- `header` : En-tête principal
- `header_top` : Zone du haut (breaking news)
- `navigation` : Menu de navigation
- `breadcrumb` : Fil d'Ariane
- `highlighted` : Zone mise en avant
- `content` : Contenu principal
- `sidebar_first` : Barre latérale gauche
- `sidebar_second` : Barre latérale droite
- `footer` : Pied de page
- `footer_first`, `footer_second`, `footer_third` : Colonnes du footer

#### Variables CSS

Le thème utilise des variables CSS pour faciliter la personnalisation :

```css
--color-primary: #CC0000;      /* Rouge principal */
--color-primary-dark: #990000;  /* Rouge foncé */
--color-secondary: #000000;     /* Noir */
--color-text: #333333;          /* Texte principal */
--color-bg: #FFFFFF;            /* Fond blanc */
--font-primary: 'Inter', ...;   /* Police principale */
--font-heading: 'Roboto Slab', ...; /* Police des titres */
```

#### Templates Twig

##### page.html.twig

Template principal de page avec structure complète incluant header, navigation, contenu et footer.

##### node--article.html.twig

Template spécialisé pour l'affichage des articles avec tous les champs :
- En-tête avec catégorie, titre, sous-titre, métadonnées
- Image à la une
- Corps de l'article
- Galerie d'images
- Vidéos
- Embeds
- Tags
- Articles liés
- Boutons de partage

## Configuration des Champs

### Type de Contenu : Article

Les articles doivent inclure les champs suivants :

| Champ | Type | Description |
|-------|------|-------------|
| `title` | Texte | Titre de l'article (obligatoire) |
| `field_subtitle` | Texte | Sous-titre / Chapeau |
| `body` | Texte long formaté | Corps de l'article avec éditeur riche |
| `field_summary` | Texte long | Résumé pour les listes |
| `field_image` | Image | Image à la une |
| `field_gallery` | Galerie | Galerie d'images |
| `field_video` | Média | Vidéos (YouTube, Vimeo, etc.) |
| `field_embeds` | Texte long formaté | Embeds multiples (Twitter, etc.) |
| `field_category` | Référence de terme | Catégorie (obligatoire) |
| `field_tags` | Référence de terme | Tags (illimités) |
| `field_author` | Référence d'entité | Auteur(s) |
| `field_related_articles` | Référence d'entité | Articles liés |
| `field_breaking_news` | Booléen | Marquer comme breaking news |
| `field_featured` | Booléen | Mettre en avant |
| `field_published_date` | Date | Date de publication |

### Taxonomies

#### Catégories

Vocabulaire hiérarchique pour organiser les articles :
- International
- Europe
- Politique
- Culture
- Sport
- Économie
- Technologie
- Santé
- Environnement

#### Tags

Vocabulaire non-hiérarchique pour le référencement interne et le tagging libre.

## Vues (Views)

### Vues à Créer

1. **Page d'accueil** (`homepage`)
   - Type : Page
   - Chemin : `/`
   - Affichage : Grille d'articles avec hero zone

2. **Carrousel** (`carousel`)
   - Type : Bloc
   - Affichage : Carrousel d'articles mis en avant

3. **Breaking News** (`breaking_news`)
   - Type : Bloc
   - Région : `header_top`
   - Filtre : `field_breaking_news = 1`

4. **Trending** (`trending`)
   - Type : Bloc
   - Tri : Par popularité (nombre de vues)

5. **Dernières nouvelles** (`latest_news`)
   - Type : Bloc
   - Tri : Par date de création (DESC)

6. **Articles par catégorie** (`category_page`)
   - Type : Page
   - Chemin : `/categorie/%`
   - Filtre : Par terme de taxonomie

## Rôles et Permissions

### Rôles

1. **Administrateur**
   - Accès complet à toutes les fonctionnalités
   - Gestion des utilisateurs et rôles
   - Configuration du site

2. **Éditeur / Chef d'édition**
   - Créer, modifier, supprimer des articles
   - Publier et dépublié des articles
   - Gérer les médias
   - Modérer les commentaires (si activé)

3. **Rédacteur**
   - Créer et modifier ses propres articles
   - Ne peut pas publier (nécessite validation)

### Permissions Recommandées

#### Pour Éditeur
- `create article content`
- `edit any article content`
- `delete any article content`
- `publish any article content`
- `unpublish any article content`
- `use media library`
- `create media`

#### Pour Rédacteur
- `create article content`
- `edit own article content`
- `delete own article content`
- `use media library`

## API et Endpoints

### Breaking News API

**Endpoint** : `/api/breaking-news`

**Méthode** : GET

**Réponse** :
```json
{
  "title": "Titre de l'article",
  "url": "https://example.com/article",
  "summary": "Résumé de l'article"
}
```

**Code d'erreur** : 404 si aucun breaking news

## Performance et Optimisation

### Cache

- Activer le cache de page en production
- Utiliser Varnish ou Redis pour le cache applicatif
- Configurer le CDN pour les médias statiques

### Images

- Utiliser des formats modernes (WebP)
- Optimiser automatiquement les images via Drupal
- Lazy loading pour les images

### CSS/JS

- Minifier les fichiers en production
- Utiliser le préprocesseur CSS/JS de Drupal
- Charger les polices de manière optimale

## Sécurité

### Recommandations

1. **HTTPS obligatoire** en production
2. **Mises à jour régulières** de Drupal et des modules
3. **Permissions strictes** sur les fichiers
4. **Backup automatique** quotidien
5. **Pare-feu applicatif (WAF)** recommandé
6. **Protection anti-DDoS** via Cloudflare ou équivalent

### Configuration

- Désactiver les modules non utilisés
- Limiter les tentatives de connexion
- Utiliser des mots de passe forts
- Activer la validation des entrées utilisateur

## Développement

### Commandes Drush Utiles

```bash
# Vider le cache
drush cr

# Mettre à jour la base de données
drush updb

# Exporter la configuration
drush config:export

# Importer la configuration
drush config:import

# Générer du contenu de test
drush devel-generate:content --types=article --num=10
```

### Débogage

- Activer le module `devel` en développement
- Utiliser `drush watchdog:show` pour voir les logs
- Activer le mode debug dans `settings.php`

## Évolutions Futures (Phase 2 & 3)

### Phase 2
- Système de commentaires avec modération
- Notifications push web (PWA)
- Tableaux de bord analytics
- Contenus sponsorisés

### Phase 3
- Espace membre avec favoris
- Newsletter automatique
- Abonnement premium
- Application mobile native

## Support et Maintenance

Pour toute question technique ou problème, consultez :
- La documentation Drupal : https://www.drupal.org/docs
- Le README.md du projet
- Le guide d'installation (INSTALLATION.md)

