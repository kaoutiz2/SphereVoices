# Module Sondage - Documentation

## 📋 Description

Le module Sondage permet de créer des sondages interactifs avec des choix multiples. Les visiteurs peuvent voter et voir les résultats en temps réel avec des pourcentages et des barres de progression.

## 🚀 Installation

Le module est intégré dans `spherevoices_core`. Pour activer le type de contenu Sondage :

1. Vider le cache Drupal :
```bash
drush cr
```

2. Le type de contenu "Sondage" sera automatiquement disponible dans le menu de création de contenu.

## 📝 Création d'un Sondage

### Depuis le Backend

1. Allez dans **Contenu** > **Ajouter du contenu** > **Sondage**
2. Remplissez les champs :
   - **Titre** : Le titre du sondage
   - **Description** : Une description optionnelle du sondage
   - **Choix du sondage** : Utilisez le widget pour ajouter des choix
     - Cliquez sur "Ajouter un choix" pour ajouter un nouveau choix
     - Cliquez sur "Supprimer" pour retirer un choix
     - Vous pouvez ajouter autant de choix que nécessaire

3. Publiez le sondage

### Structure des Choix

Les choix sont stockés au format JSON avec la structure suivante :
```json
[
  {
    "text": "Choix 1",
    "votes": 0
  },
  {
    "text": "Choix 2",
    "votes": 0
  }
]
```

## 🎨 Affichage

### Bloc Sondage

Le bloc sondage s'affiche automatiquement sur la page d'accueil, sous le bloc Agenda dans la sidebar droite.

Le bloc affiche :
- Le titre du sondage
- La description (si présente)
- Les choix avec des boutons pour voter
- Les résultats en temps réel avec :
  - Le nombre de votes pour chaque choix
  - Le pourcentage de votes
  - Une barre de progression visuelle

### Fonctionnalités

- **Vote en temps réel** : Les votes sont enregistrés immédiatement via AJAX
- **Mise à jour automatique** : Les résultats (votes, pourcentages, barres) se mettent à jour automatiquement après chaque vote
- **Interface intuitive** : Boutons clairs et résultats visuels

## 🔧 Fichiers Créés

### Configuration Drupal

- `node.type.poll.yml` - Type de contenu Sondage
- `field.storage.node.field_poll_description.yml` - Stockage du champ description
- `field.storage.node.field_poll_choices.yml` - Stockage du champ choix
- `field.field.node.poll.field_poll_description.yml` - Instance du champ description
- `field.field.node.poll.field_poll_choices.yml` - Instance du champ choix
- `core.entity_form_display.node.poll.default.yml` - Formulaire d'édition
- `core.entity_view_display.node.poll.default.yml` - Affichage

### Code PHP

- `src/Plugin/Field/FieldWidget/PollChoicesWidget.php` - Widget personnalisé pour éditer les choix
- `src/Plugin/Block/PollBlock.php` - Bloc d'affichage du sondage
- `src/Controller/PollController.php` - Contrôleur pour gérer les votes AJAX

### Assets

- `js/poll_choices_widget.js` - JavaScript pour le widget d'édition
- `js/poll_block.js` - JavaScript pour le bloc de vote
- `css/poll_block.css` - Styles CSS pour le bloc

### Templates

- Le bloc est intégré dans `page--front.html.twig` (sidebar droite, sous l'agenda)

## 🛠️ Développement

### Route AJAX

La route `/api/poll/vote` permet de voter via AJAX :

**Méthode** : POST
**Paramètres** :
- `poll_id` : ID du nœud sondage
- `choice_index` : Index du choix (0, 1, 2, ...)

**Réponse JSON** :
```json
{
  "success": true,
  "total_votes": 10,
  "results": [
    {
      "index": 0,
      "text": "Choix 1",
      "votes": 5,
      "percentage": 50.0
    },
    {
      "index": 1,
      "text": "Choix 2",
      "votes": 5,
      "percentage": 50.0
    }
  ]
}
```

### Personnalisation

Pour personnaliser l'apparence du bloc, modifiez :
- `css/poll_block.css` - Styles CSS
- `src/Plugin/Block/PollBlock.php` - Structure du bloc

## 📌 Notes

- Le sondage le plus récent et publié est automatiquement affiché
- Les votes sont stockés directement dans le champ JSON du nœud
- Pas de limitation de votes par utilisateur (peut être ajouté si nécessaire)
- Le cache est désactivé pour le bloc afin d'afficher les résultats en temps réel

## 🔄 Mise à jour

Pour mettre à jour le module après des modifications :

```bash
drush cr
drush updb
```

## 🆘 Support

En cas de problème, vérifiez :
- Les logs Drupal : `drush watchdog:show`
- Le cache : `drush cr`
- Les permissions : Assurez-vous que les utilisateurs ont accès au contenu
