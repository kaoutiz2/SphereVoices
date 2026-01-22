# Changelog - Module Agenda

## [2026-01-11] - Ajout du module Agenda

### Ajouté

#### Type de contenu
- Nouveau type de contenu "Événement" (`event`)
  - Champ titre (title)
  - Champ date (field_event_date) - Date uniquement
  - Champ description (body) - Texte enrichi

#### Affichages
- **Bloc Agenda** sur la page d'accueil
  - Positionné sous le bloc Brèves dans la sidebar droite
  - Affiche les 5 prochains événements à venir
  - Lien "Afficher plus" vers la page complète
  
- **Page Agenda** (`/agenda`)
  - Liste complète de tous les événements à venir
  - Barre de recherche par titre
  - Filtrage par période (date début/fin)
  - Affichage en grille responsive
  - Pagination

- **Page Agenda avec navigation par mois** (`/agenda-mois`)
  - Navigation intuitive entre les mois (précédent/suivant)
  - Affichage des événements du mois sélectionné
  - Formulaire de recherche intégré

#### Fichiers créés

**Configuration Drupal (www/modules/custom/spherevoices_core/config/install/)**
- `node.type.event.yml`
- `field.storage.node.field_event_date.yml`
- `field.field.node.event.field_event_date.yml`
- `field.field.node.event.body.yml`
- `core.entity_form_display.node.event.default.yml`
- `core.entity_view_display.node.event.default.yml`
- `core.entity_view_display.node.event.teaser.yml`
- `views.view.agenda.yml`

**Code PHP**
- `www/modules/custom/spherevoices_core/src/Controller/AgendaController.php` - Contrôleur pour la navigation par mois
- `www/modules/custom/spherevoices_core/src/Form/AgendaSearchForm.php` - Formulaire de recherche
- `www/modules/custom/spherevoices_core/scripts/generate_events.php` - Script de génération d'événements de démo

**Templates Twig (Thème)**
- `www/themes/custom/spherevoices_theme/templates/content/node--event--teaser.html.twig` - Affichage teaser
- `www/themes/custom/spherevoices_theme/templates/content/node--event--full.html.twig` - Affichage complet
- `www/themes/custom/spherevoices_theme/templates/views/views-view--agenda--page-agenda.html.twig` - Page agenda
- `www/themes/custom/spherevoices_theme/templates/views/views-exposed-form--agenda.html.twig` - Formulaires de filtrage

**Templates Twig (Module)**
- `www/modules/custom/spherevoices_core/templates/agenda-page.html.twig` - Page avec navigation par mois

**Documentation**
- `AGENDA_MODULE.md` - Documentation complète du module
- `install-agenda.sh` - Script d'installation automatique

#### Fichiers modifiés

**Logique métier**
- `www/themes/custom/spherevoices_theme/spherevoices_theme.theme`
  - Ajout de la logique de chargement des événements pour le bloc Agenda
  - Filtrage des événements futurs uniquement

**Templates**
- `www/themes/custom/spherevoices_theme/templates/layout/page--front.html.twig`
  - Ajout du bloc Agenda sous le bloc Brèves dans la sidebar

**Styles CSS**
- `www/themes/custom/spherevoices_theme/css/components.css`
  - Section "AGENDA STYLES" complète
  - Styles pour le bloc agenda
  - Styles pour les événements (teaser et full)
  - Styles pour la page agenda avec recherche
  - Styles pour la navigation par mois
  - Styles responsive

**Configuration du module**
- `www/modules/custom/spherevoices_core/spherevoices_core.module`
  - Ajout du hook_theme() pour le template agenda-page
  
- `www/modules/custom/spherevoices_core/spherevoices_core.routing.yml`
  - Ajout de la route `/agenda-mois`

### Caractéristiques techniques

#### Design
- Style cohérent avec le reste du site
- Affichage de la date dans un bloc coloré (jour + mois)
- Effet hover sur les événements
- Design responsive pour mobile et tablette

#### Performance
- Requêtes optimisées (filtre sur les événements futurs)
- Cache Drupal utilisé
- Pagination pour éviter de charger trop d'événements

#### UX
- Navigation intuitive entre les mois
- Recherche facile par titre
- Filtrage par période pour affiner les résultats
- Affichage clair des dates

### Installation

```bash
# Option 1: Script automatique
./install-agenda.sh

# Option 2: Installation manuelle
cd www
drush pm:uninstall spherevoices_core -y
drush pm:enable spherevoices_core -y
drush cr
drush php:script modules/custom/spherevoices_core/scripts/generate_events.php
drush router:rebuild
drush cr
```

### Notes de développement

- Les événements utilisent le système de permissions standard de Drupal
- Le champ date utilise le type `datetime` avec `datetime_type: date` (date seule, sans heure)
- Le bloc Agenda affiche uniquement les événements futurs (date >= aujourd'hui)
- La page `/agenda` utilise Views pour plus de flexibilité
- La page `/agenda-mois` utilise un contrôleur personnalisé pour la navigation

### TODO Future
- [ ] Ajouter un calendrier visuel (vue calendrier)
- [ ] Export iCal des événements
- [ ] Notifications par email pour les événements à venir
- [ ] Intégration avec Google Calendar
- [ ] Système de réservation/inscription aux événements
- [ ] Catégories d'événements
- [ ] Images pour les événements
- [ ] Localisation des événements (adresse, carte)

