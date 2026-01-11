# Guide rapide - Module Agenda

## 🚀 Installation en 1 commande

```bash
./install-agenda.sh
```

C'est tout ! Le script va :
1. ✅ Réinstaller le module avec les nouvelles fonctionnalités
2. ✅ Nettoyer le cache
3. ✅ Créer 15 événements de démonstration
4. ✅ Configurer les routes

## 📍 Où trouver l'Agenda ?

### Sur la page d'accueil
- **Bloc Agenda** dans la sidebar droite, sous les brèves
- Affiche les 5 prochains événements
- Cliquez sur "Afficher plus" pour voir tous les événements

### Page complète
- **URL**: `/agenda`
- Recherche par titre
- Filtrage par date
- Vue en grille

### Navigation par mois
- **URL**: `/agenda-mois`
- Navigation mois par mois
- Recherche intégrée

## ➕ Créer un événement

### Via l'interface admin

1. Allez dans **Contenu** → **Ajouter du contenu** → **Événement**
2. Remplissez :
   - **Titre** : Nom de l'événement
   - **Date** : Date de l'événement
   - **Description** : Détails de l'événement
3. Cliquez sur **Enregistrer**

### Via Drush (pour le développement)

Le script `generate_events.php` crée des événements de test :

```bash
cd www
../vendor/bin/drush php:script modules/custom/spherevoices_core/scripts/generate_events.php
```

## 🎨 Aperçu du design

### Bloc Agenda (sidebar)
```
┌─────────────────────────┐
│      AGENDA             │
├─────────────────────────┤
│ ┌──┐                    │
│ │12│ Conférence climat  │
│ │JAN│ Description...    │
│ └──┘                    │
│                         │
│ ┌──┐                    │
│ │15│ Concert classique │
│ │JAN│ Description...    │
│ └──┘                    │
├─────────────────────────┤
│   [Afficher plus]       │
└─────────────────────────┘
```

### Page Agenda
- **Barre de recherche** en haut
- **Filtres** par période
- **Grille d'événements** responsive
- **Pagination**

## 🔧 Personnalisation

### Modifier le nombre d'événements dans le bloc

Fichier : `www/themes/custom/spherevoices_theme/spherevoices_theme.theme`

Ligne ~617 :
```php
->range(0, 5) // Changer 5 par le nombre souhaité
```

### Modifier les couleurs

Fichier : `www/themes/custom/spherevoices_theme/css/components.css`

Section "AGENDA STYLES" (~ligne 926+)

Variables CSS utilisées :
- `var(--color-primary)` : Couleur principale
- `var(--color-secondary)` : Couleur secondaire
- `var(--color-border)` : Bordures
- `var(--color-bg-light)` : Arrière-plans clairs

## 🐛 Dépannage rapide

### Le bloc n'apparaît pas
```bash
cd www
../vendor/bin/drush cr
```

### Erreur 404 sur /agenda
```bash
cd www
../vendor/bin/drush router:rebuild
../vendor/bin/drush cr
```

### Les événements ne s'affichent pas
Vérifiez que :
1. Les événements sont **publiés** (status = 1)
2. Les événements ont une **date future**
3. Le cache est vidé

```bash
cd www
# Vérifier les événements
../vendor/bin/drush sql:query "SELECT nid, title, status FROM node_field_data WHERE type='event'"

# Vider le cache
../vendor/bin/drush cr
```

## 📊 Statistiques

Après l'installation avec le script de démonstration :
- ✅ 15 événements créés
- ✅ 3 pages fonctionnelles (accueil, /agenda, /agenda-mois)
- ✅ 2 modes d'affichage (teaser, full)
- ✅ 1 vue Views configurée
- ✅ Responsive design

## 📞 Besoin d'aide ?

Consultez la documentation complète : `AGENDA_MODULE.md`

Vérifier les logs :
```bash
cd www
../vendor/bin/drush watchdog:show --severity=Error
```

