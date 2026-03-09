# Rendre le champ vidéo visible dans le champ Corps (articles)

## Activer le bouton Média et la vidéo (sans importer de config)

L’import de configuration provoque une erreur « site différent ». Il faut donc tout faire **dans l’interface Drupal**.

**Ordre obligatoire :** active d’abord le **filtre** (Étape A), enregistre, puis ajoute le **bouton** (Étape B). Si tu vois *« L’élément Drupal media de la barre d’outils requiert le filtre Intégrer un média pour être activé »*, c’est que le filtre n’est pas encore activé : fais l’Étape A, enregistre, puis refais l’Étape B.

*(Les messages « Deprecated function: Using null as an array offset » viennent du noyau Drupal/PHP ; tu peux les ignorer, ils n’empêchent pas la configuration.)*

### Étape A : Activer le filtre « Intégrer un média » (d’abord)

1. Va dans **Configuration** → **Création de contenu** → **Formats de texte et éditeurs**  
   (ou `/admin/config/content/formats`).
2. Clique sur **Configurer** à droite de **HTML complet**.
3. Dans la section **Filtres**, coche **« Intégrer un média »** (ou **« Intégrer des médias »** / **« Embed media »**) pour qu’il soit **activé**.
4. Si le filtre a des paramètres (petite flèche ou « Configurer »), ouvre-les et autorise au moins **Vidéo** (et Image, etc. si tu veux).
5. Clique sur **Enregistrer la configuration** en bas de la page.

### Étape B : Ajouter le bouton Média dans la barre (après avoir enregistré l’étape A)

1. Retourne sur **Formats de texte et éditeurs** → **Configurer** pour **HTML complet**.
2. Repère la section **Barre d’outils** pour l’éditeur CKEditor 5.
3. Dans les **boutons disponibles**, trouve **« Média »** (ou **Insert media** / **drupalMedia**).
4. **Glisse-dépose** ce bouton dans la **barre d’outils active** (par ex. à côté de « Insérer une image »).
5. Clique sur **Enregistrer la configuration**.

### Étape C : Vider le cache

**Configuration** → **Développement** → **Vider tous les caches**.

Ensuite, en créant ou modifiant un article avec le champ **Corps** en format **HTML complet**, tu dois voir le bouton **Média** dans la barre. En cliquant dessus → **Créer** → **Vidéo**, le formulaire avec le champ « Fichier vidéo » s’affiche (si tu as bien fait la Méthode 1 ci-dessous).

**Si l’image s’affiche dans le corps mais pas la vidéo :** le thème affiche maintenant la vidéo via un template dédié (`media--video.html.twig`). Vider le cache. Si ça ne suffit pas, vérifier que dans le filtre « Intégrer un média » (paramètres du format HTML complet), le type **Vidéo** est bien autorisé.

---

## Méthode 1 : Champ « Fichier vidéo » dans le formulaire Média (Vidéo)

1. Va dans **Structure** → **Types de médias**  
   (ou `/admin/structure/media`).

2. Clique sur **Vidéo** (ou sur « Gérer l’affichage des formulaires » à côté de Vidéo).

3. Onglet **« Gérer l’affichage des formulaires »**  
   (ou lien « Form display » / « Affichage du formulaire »).

4. En haut de la page, repère le sélecteur **« Mode d’affichage du formulaire »** ou **« Form mode »** :
   - Choisis **« Médiathèque »** (ou **« Media library »**).

5. Dans la liste des champs, trouve **« Video file »** / **« Fichier vidéo »** :
   - S’il est dans la zone **« Désactivé »** (en bas), fais un **glisser-déposer** pour le remonter dans la région **« Contenu »**.
   - Ou clique sur le petit **crayon** à côté du champ et assure-toi qu’il n’est pas coché comme « caché » / « hidden ».

6. **Enregistre** en bas de la page.

Ensuite, vide le cache : **Configuration** → **Développement** → **Vider tous les caches**.

---

## Méthode 2 : Vérifier le format du champ Corps

Pour que le bouton **Média** (et donc la vidéo) apparaisse dans le champ Corps :

1. **Configuration** → **Contenu** → **Types de contenu** → **Article** → **Gérer l’affichage des formulaires**.

2. Repère le champ **« Corps »** :
   - Le **type de widget** doit être un éditeur de texte riche (pas seulement « Zone de texte avec résumé » sans format).
   - En général, le format **« HTML complet »** est proposé dans une liste déroulante sous le champ ; il doit être **sélectionnable** (et de préférence choisi par défaut).

3. En édition d’un article, sous le champ Corps, vérifie que le **format** est bien **« HTML complet »**.  
   C’est ce format qui affiche la barre d’outils avec **Insérer une image** et **Média** (icône médiathèque).  
   Si tu ne vois pas le format, va dans **Configuration** → **Contenu** → **Formats de texte** et vérifie que « HTML complet » existe et qu’il utilise l’éditeur CKEditor 5 avec le bouton Média.

---

## Résumé

- **Vidéo dans le formulaire Média** : Configuration → Médias → Types de médias → **Vidéo** → Gérer l’affichage des formulaires → mode **Médiathèque** → champ **Fichier vidéo** visible et déplacé dans « Contenu ».
- **Bouton Média dans le Corps** : champ Corps avec format **HTML complet** (éditeur riche avec bouton Média).
- **Clic miniature → article** : le thème a été corrigé pour que le clic sur la miniature (vidéo ou image) en page d’accueil redirige bien vers l’article (plus de blocage JavaScript).
- **Vidéo dans le corps de l’article** : le template affiche explicitement le champ Corps dans une zone `.article-body`. Si la vidéo n’apparaît pas, vérifier que l’affichage « Complet » de l’article affiche bien le champ **Corps** avec le format **HTML complet** (Configuration → Types de contenu → Article → Gérer l’affichage → mode Complet).
- Penser à **vider le cache** après les changements.
