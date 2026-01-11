# Module Agenda - SphereVoices

## Description

Le module Agenda permet de gérer et d'afficher des événements sur le site SphereVoices. Les événements sont affichés dans un bloc sur la page d'accueil (sous les brèves) et sur une page dédiée avec des fonctionnalités de recherche et de navigation.

## Fonctionnalités

### Type de contenu "Événement"
- **Titre** : Le nom de l'événement
- **Date** : La date de l'événement
- **Description** : Le contenu détaillé de l'événement

### Affichage sur la page d'accueil
- Bloc "Agenda" affiché dans la sidebar droite, sous le bloc "Brèves"
- Affiche les 5 prochains événements à venir
- Lien "Afficher plus" vers la page complète

### Page Agenda complète (`/agenda`)
- Liste tous les événements à venir
- Barre de recherche par titre
- Filtrage par période (date de début et date de fin)
- Affichage en grille responsive
- Pagination

### Navigation par mois (`/agenda-mois`)
- Navigation entre les différents mois
- Affichage des événements du mois sélectionné
- Barre de recherche intégrée

## Installation

### 1. Réinstaller le module

Si le module est déjà installé, vous devez d'abord le désinstaller puis le réinstaller pour que les nouvelles configurations soient prises en compte :

```bash
cd /Users/bryangast/Documents/Kaoutiz.dev/SphereVoices/site/www
drush pm:uninstall spherevoices_core -y
drush pm:enable spherevoices_core -y
drush cr
```

### 2. Générer des événements de démonstration

Pour tester le module avec des données de démonstration :

```bash
drush php:script modules/custom/spherevoices_core/scripts/generate_events.php
```

### 3. Vider le cache

```bash
drush cr
```

## Utilisation

### Créer un événement

1. Aller dans **Contenu** > **Ajouter du contenu** > **Événement**
2. Remplir les champs :
   - Titre de l'événement
   - Date de l'événement
   - Description
3. Cliquer sur **Enregistrer**

### Gérer les événements

- **Liste des événements** : Contenu > Filtrer par type "Événement"
- **Modifier un événement** : Cliquer sur "Modifier" depuis la liste
- **Supprimer un événement** : Cliquer sur "Supprimer" depuis la liste

### Pages accessibles

- **Page d'accueil** : Bloc Agenda dans la sidebar droite
- **Page Agenda** : `/agenda` - Liste complète avec recherche
- **Agenda par mois** : `/agenda-mois` - Navigation par mois

## Structure des fichiers

### Configuration (config/install/)
- `node.type.event.yml` - Type de contenu Événement
- `field.storage.node.field_event_date.yml` - Champ de stockage de la date
- `field.field.node.event.field_event_date.yml` - Configuration du champ date
- `field.field.node.event.body.yml` - Configuration du champ description
- `core.entity_form_display.node.event.default.yml` - Affichage du formulaire
- `core.entity_view_display.node.event.default.yml` - Affichage par défaut
- `core.entity_view_display.node.event.teaser.yml` - Affichage teaser
- `views.view.agenda.yml` - Vue pour l'agenda

### Contrôleurs (src/Controller/)
- `AgendaController.php` - Contrôleur pour la page avec navigation par mois

### Formulaires (src/Form/)
- `AgendaSearchForm.php` - Formulaire de recherche

### Templates
- Thème : `templates/content/node--event--teaser.html.twig`
- Thème : `templates/content/node--event--full.html.twig`
- Thème : `templates/views/views-view--agenda--page-agenda.html.twig`
- Thème : `templates/views/views-exposed-form--agenda.html.twig`
- Module : `templates/agenda-page.html.twig`

### Scripts
- `scripts/generate_events.php` - Génération d'événements de démonstration

### CSS
- `themes/custom/spherevoices_theme/css/components.css` - Styles pour l'agenda

## Personnalisation

### Modifier le nombre d'événements affichés dans le bloc

Éditer le fichier `spherevoices_theme.theme` ligne ~617 :

```php
->range(0, 5) // Changer 5 par le nombre souhaité
```

### Modifier les styles

Les styles de l'agenda se trouvent dans :
`themes/custom/spherevoices_theme/css/components.css`

Rechercher la section "AGENDA STYLES"

### Modifier le format de date

Éditer le template `node--event--teaser.html.twig` :

```twig
{{ node.field_event_date.value|date('d/m/Y') }}
```

## Permissions

Les événements utilisent les permissions standard de Drupal pour les nœuds :

- **Créer des événements** : Permission "Create event content"
- **Modifier ses propres événements** : Permission "Edit own event content"
- **Modifier tous les événements** : Permission "Edit any event content"
- **Supprimer des événements** : Permissions "Delete own/any event content"

## Dépannage

### Le bloc Agenda n'apparaît pas

1. Vérifier que des événements futurs existent
2. Vider le cache : `drush cr`
3. Vérifier le fichier `spherevoices_theme.theme` pour la logique d'affichage

### Les événements ne s'affichent pas dans le bon ordre

Vérifier le tri dans la vue : Configuration > Vues > Agenda > Modifier

### Erreur 404 sur /agenda

1. Vider le cache : `drush cr`
2. Reconstruire le routage : `drush router:rebuild`

## Support

Pour toute question ou problème, consulter les logs Drupal :

```bash
drush watchdog:show --severity=Error
```

