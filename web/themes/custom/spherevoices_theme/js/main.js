/**
 * @file
 * JavaScript principal pour le thème SphereVoices.
 */

(function (Drupal, jQuery) {
  'use strict';
  
  // Attendre que Drupal soit complètement chargé
  if (typeof Drupal === 'undefined' || typeof jQuery === 'undefined') {
    console.warn('Drupal or jQuery not loaded yet');
    return;
  }

  /**
   * Comportements du thème.
   */
      Drupal.behaviors.spherevoicesTheme = {
        attach: function (context, settings) {
          // Forcer l'affichage des champs de formulaire utilisateur
          this.fixUserFormInputs(context);
          // Exécuter aussi après plusieurs délais pour s'assurer que le DOM est complètement chargé
          var self = this;
          setTimeout(function() {
            self.fixUserFormInputs(context);
          }, 50);
          setTimeout(function() {
            self.fixUserFormInputs(context);
          }, 200);
          setTimeout(function() {
            self.fixUserFormInputs(context);
          }, 500);
          setTimeout(function() {
            self.fixUserFormInputs(context);
          }, 1000);
          
          // Forcer la toolbar en mode vertical par défaut
          this.forceVerticalToolbar(context);
          
          // Sur mobile, cacher le menu par défaut IMMÉDIATEMENT
          if (window.innerWidth <= 767) {
            const mainNav = context.querySelector('.main-navigation');
            const menuBlock = context.querySelector('.menu.menu--main');
            if (mainNav) {
              mainNav.classList.add('mobile-menu-hidden');
              mainNav.style.display = 'none';
              mainNav.style.visibility = 'hidden';
              mainNav.style.opacity = '0';
              mainNav.style.height = '0';
              mainNav.style.width = '0';
              mainNav.style.overflow = 'hidden';
              mainNav.style.position = 'absolute';
              mainNav.style.left = '-9999px';
              mainNav.style.pointerEvents = 'none';
            }
            if (menuBlock) {
              menuBlock.style.display = 'none';
              menuBlock.style.visibility = 'hidden';
              menuBlock.style.opacity = '0';
              menuBlock.style.height = '0';
              menuBlock.style.width = '0';
              menuBlock.style.overflow = 'hidden';
              menuBlock.style.position = 'absolute';
              menuBlock.style.left = '-9999px';
              menuBlock.style.pointerEvents = 'none';
            }
          }

          // Chargement du Breaking News
          this.loadBreakingNews(context);

          // Initialisation du carrousel
          this.initCarousel(context);

          // Gestion du menu mobile
          this.initMobileMenu(context);

          // Gestion du scroll pour la navigation sticky
          this.handleStickyNav(context);
        },

        /**
         * Force l'affichage des champs de formulaire utilisateur.
         */
        fixUserFormInputs: function (context) {
          // Fonction pour injecter un input directement dans un form-item
          const injectInputInFormItem = function(formItem, name, type, autocomplete) {
            if (!formItem) return;
            
            // Ne pas injecter dans les descriptions ou les éléments details
            if (formItem.classList.contains('description') || formItem.closest('.description') || formItem.tagName === 'DETAILS') {
              return;
            }
            
            const inputId = 'edit-' + name;
            // Vérifier si l'input existe déjà dans le form-item
            if (formItem.querySelector('#' + inputId) || formItem.querySelector('input[name="' + name + '"]')) {
              return;
            }
            
            // Vérifier que ce n'est pas un form-item pour contact ou user_picture
            const classes = formItem.className || '';
            if (classes.includes('form-item-contact') || classes.includes('form-item-user-picture') || classes.includes('js-form-item-contact') || classes.includes('js-form-item-user-picture')) {
              return;
            }
            
            // Créer l'input
            const input = document.createElement('input');
            input.type = type;
            input.name = name;
            input.id = inputId;
            input.className = 'form-text';
            input.required = true;
            input.setAttribute('autocomplete', autocomplete);
            input.size = 60;
            if (type === 'password') {
              input.maxLength = 128;
            } else if (type === 'email') {
              input.maxLength = 254;
            } else {
              input.maxLength = 60;
            }
            input.style.cssText = 'display: block !important; visibility: visible !important; opacity: 1 !important; width: 100% !important; padding: 0.5rem 1rem !important; border: 1px solid #ccc !important; background: white !important; min-height: 2.5rem !important; margin-top: 0.5rem !important;';
            
            // Trouver le texte "Nom d'utilisateur" ou "Mot de passe" dans le form-item
            const textNodes = [];
            const walker = document.createTreeWalker(
              formItem,
              NodeFilter.SHOW_TEXT,
              null,
              false
            );
            let node;
            while (node = walker.nextNode()) {
              if (node.textContent.trim()) {
                textNodes.push(node);
              }
            }
            
            // Trouver le nœud texte qui correspond au label
            let targetTextNode = null;
            textNodes.forEach(function(textNode) {
              const text = textNode.textContent.trim().toLowerCase();
              if ((name === 'name' && (text.includes('nom') || text.includes('username') || text.includes('utilisateur'))) ||
                  (name === 'pass' && (text.includes('mot de passe') || text.includes('password'))) ||
                  (name === 'mail' && (text.includes('email') || text.includes('e-mail') || text.includes('courriel')))) {
                targetTextNode = textNode;
              }
            });
            
            if (targetTextNode) {
              // Insérer l'input après le nœud texte
              const parent = targetTextNode.parentNode;
              if (parent) {
                // Créer un saut de ligne si nécessaire
                const br = document.createElement('br');
                parent.insertBefore(br, targetTextNode.nextSibling);
                parent.insertBefore(input, br.nextSibling);
              } else {
                formItem.appendChild(input);
              }
            } else {
              // Pas de texte trouvé, ajouter à la fin du form-item
              formItem.appendChild(input);
            }
          };
          
          // Trouver directement les form-items par leurs classes CSS (utiliser document pour être sûr)
          // Utiliser querySelectorAll pour trouver tous les form-items possibles
          const allFormItems = document.querySelectorAll('.form-item-name, .js-form-item-name, .form-item-pass, .js-form-item-pass, .form-item-mail, .js-form-item-mail, .form-item-user-picture, .js-form-item-user-picture, .form-item-contact, .js-form-item-contact, [class*="user-picture"], [class*="user_picture"]');
          
          // Chercher aussi les form-items pour user_picture avec différentes variantes
          const userPictureItems = document.querySelectorAll('[class*="user-picture"], [class*="user_picture"], [id*="user-picture"], [id*="user_picture"]');
          
          // Trouver le conteneur du formulaire (block-spherevoices-theme-content)
          const formContainer = document.querySelector('#block-spherevoices-theme-content');
          
          // Chercher d'abord les champs cachés existants dans le document
          const existingFormId = document.querySelector('input[name="form_id"]');
          const existingFormBuildId = document.querySelector('input[name="form_build_id"]');
          const existingFormToken = document.querySelector('input[name="form_token"]');
          
          // Si le formulaire n'existe pas, le créer
          let form = document.querySelector('form#user-login-form, form#user-register-form, form#user-pass');
          const currentPath = window.location.pathname;
          
          // Vérifier si on est vraiment sur une page de formulaire utilisateur
          const isUserFormPage = currentPath.includes('/user/login') || 
                                 currentPath.includes('/user/register') || 
                                 currentPath.includes('/user/password') ||
                                 currentPath === '/user/login' ||
                                 currentPath === '/user/register' ||
                                 currentPath === '/user/password';
          
          // Ne créer le formulaire QUE si on est sur une page de formulaire utilisateur
          if (!form && formContainer && isUserFormPage) {
            // Déterminer l'ID et l'action du formulaire selon la page
            let formId = 'user-login-form';
            let formAction = '/user/login';
            let formIdValue = 'user_login_form';
            if (currentPath.includes('/user/register')) {
              formId = 'user-register-form';
              formAction = '/user/register';
              formIdValue = 'user_register_form';
            } else if (currentPath.includes('/user/password')) {
              formId = 'user-pass';
              formAction = '/user/password';
              formIdValue = 'user_pass';
            }
            
            // Créer le formulaire
            form = document.createElement('form');
            form.id = formId;
            form.setAttribute('action', formAction);
            form.setAttribute('method', 'post');
            form.setAttribute('accept-charset', 'UTF-8');
            // Pour le formulaire d'inscription, ajouter enctype pour l'upload de fichiers
            if (currentPath.includes('/user/register')) {
              form.setAttribute('enctype', 'multipart/form-data');
            }
            form.className = 'user-login-form user-form';
            
            // Ajouter les champs cachés nécessaires AVANT de déplacer les form-items
            // form_id
            const formIdInput = document.createElement('input');
            formIdInput.type = 'hidden';
            formIdInput.name = 'form_id';
            formIdInput.value = existingFormId ? existingFormId.value : formIdValue;
            formIdInput.id = existingFormId ? existingFormId.id : 'edit-' + formIdValue;
            form.appendChild(formIdInput);
            
            // form_build_id
            const buildIdInput = document.createElement('input');
            buildIdInput.type = 'hidden';
            buildIdInput.name = 'form_build_id';
            buildIdInput.value = existingFormBuildId ? existingFormBuildId.value : 'form-' + Math.random().toString(36).substring(2, 15) + Math.random().toString(36).substring(2, 15);
            buildIdInput.id = existingFormBuildId ? existingFormBuildId.id : 'edit-form-build-id';
            buildIdInput.setAttribute('autocomplete', 'off');
            form.appendChild(buildIdInput);
            
            // form_token (si présent, pour utilisateurs authentifiés)
            if (existingFormToken) {
              const tokenInput = document.createElement('input');
              tokenInput.type = 'hidden';
              tokenInput.name = 'form_token';
              tokenInput.value = existingFormToken.value;
              tokenInput.id = existingFormToken.id;
              form.appendChild(tokenInput);
            }
            
            // Déplacer tous les form-items dans le formulaire
            if (formContainer) {
              const formItems = formContainer.querySelectorAll('.form-item, .js-form-item, .form-actions');
              formItems.forEach(function(item) {
                form.appendChild(item);
              });
              
              // Insérer le formulaire dans le conteneur (seulement sur les pages de formulaire)
              formContainer.innerHTML = '';
              formContainer.appendChild(form);
            }
          }
          
          // Fonction pour injecter un champ file upload (image)
          const injectFileUploadInFormItem = function(formItem, name) {
            if (!formItem) return;
            
            const inputId = 'edit-' + name.replace(/_/g, '-') + '-0-upload';
            // Vérifier si l'input file existe déjà (dans le form-item ou ailleurs)
            if (formItem.querySelector('#' + inputId) || formItem.querySelector('input[type="file"][name*="' + name + '"]') || document.getElementById(inputId)) {
              return;
            }
            
            // Créer le conteneur pour le champ file
            const fileWrapper = document.createElement('div');
            fileWrapper.className = 'js-form-managed-file form-managed-file';
            
            // Créer l'input file
            const fileInput = document.createElement('input');
            fileInput.type = 'file';
            // Drupal utilise files[FIELD_NAME_0] pour les champs d'image (ex: files[user_picture_0])
            const fieldNameForFiles = name.replace(/-/g, '_') + '_0';
            fileInput.name = 'files[' + fieldNameForFiles + ']';
            fileInput.id = inputId;
            fileInput.className = 'form-file';
            fileInput.setAttribute('data-drupal-selector', name.replace(/_/g, '-') + '-0-upload');
            fileInput.accept = '.png,.gif,.jpg,.jpeg,.webp,image/png,image/gif,image/jpeg,image/jpg,image/webp';
            fileInput.style.cssText = 'display: block !important; visibility: visible !important; opacity: 1 !important; width: 100% !important; padding: 0.5rem 1rem !important; border: 1px solid #ccc !important; background: white !important; min-height: 2.5rem !important; margin-top: 0.5rem !important;';
            
            fileWrapper.appendChild(fileInput);
            
            // Vérifier si la description existe déjà dans le form-item pour éviter les doublons
            const existingDescription = formItem.querySelector('.description');
            if (!existingDescription || !existingDescription.textContent.includes('1 seul fichier')) {
              const description = document.createElement('div');
              description.className = 'description';
              description.innerHTML = '1 seul fichier.<br>Limité à 2 Mo.<br>Types autorisés : png gif jpg jpeg webp.';
              description.style.cssText = 'margin-top: 0.5rem !important; font-size: 0.875rem !important; color: #666 !important;';
              fileWrapper.appendChild(description);
            }
            
            // Trouver le label ou le texte dans le form-item
            const label = formItem.querySelector('label');
            if (label) {
              label.parentNode.insertBefore(fileWrapper, label.nextSibling);
            } else {
              // Chercher le texte "Image" ou "visage" ou "picture"
              const textNodes = [];
              const walker = document.createTreeWalker(
                formItem,
                NodeFilter.SHOW_TEXT,
                null,
                false
              );
              let node;
              while (node = walker.nextNode()) {
                const text = node.textContent.trim().toLowerCase();
                if (text && (text.includes('image') || text.includes('visage') || text.includes('picture') || text.includes('virtuel'))) {
                  textNodes.push(node);
                }
              }
              
              if (textNodes.length > 0) {
                const parent = textNodes[0].parentNode;
                if (parent) {
                  const br = document.createElement('br');
                  parent.insertBefore(br, textNodes[0].nextSibling);
                  parent.insertBefore(fileWrapper, br.nextSibling);
                } else {
                  formItem.appendChild(fileWrapper);
                }
              } else {
                formItem.appendChild(fileWrapper);
              }
            }
          };
          
          // Fonction pour injecter une case à cocher (checkbox)
          const injectCheckboxInFormItem = function(formItem, name) {
            if (!formItem) return;
            
            const inputId = 'edit-' + name.replace(/_/g, '-');
            // Vérifier si l'input checkbox existe déjà (dans le form-item ou ailleurs)
            if (formItem.querySelector('#' + inputId) || formItem.querySelector('input[type="checkbox"][name="' + name + '"]') || document.getElementById(inputId)) {
              return;
            }
            
            // Vérifier aussi si le texte de description existe déjà dans le form-item pour éviter les doublons
            const existingText = formItem.textContent;
            const textToCheck = 'Permettre aux autres utilisateurs de vous contacter';
            const occurrences = (existingText.match(new RegExp(textToCheck, 'g')) || []).length;
            if (occurrences >= 2) {
              // Le texte apparaît déjà plusieurs fois, ne pas ajouter
              return;
            }
            
            // Créer le conteneur pour la checkbox
            const checkboxWrapper = document.createElement('div');
            checkboxWrapper.className = 'js-form-item form-item js-form-type-checkbox form-type-checkbox form-item-contact js-form-item-contact';
            checkboxWrapper.style.cssText = 'display: block !important; visibility: visible !important; opacity: 1 !important;';
            
            // Créer la checkbox
            const checkbox = document.createElement('input');
            checkbox.type = 'checkbox';
            checkbox.name = name;
            checkbox.id = inputId;
            checkbox.className = 'form-checkbox';
            checkbox.setAttribute('data-drupal-selector', name.replace(/_/g, '-'));
            checkbox.value = '1';
            checkbox.style.cssText = 'display: inline-block !important; visibility: visible !important; opacity: 1 !important; margin-right: 0.5rem !important; width: auto !important; height: auto !important; cursor: pointer !important; position: relative !important; z-index: 9999 !important; pointer-events: auto !important;';
            checkbox.disabled = false;
            checkbox.readOnly = false;
            
            // Ajouter un event listener pour s'assurer que la checkbox fonctionne
            checkbox.addEventListener('click', function(e) {
              e.stopPropagation();
            }, true);
            
            // Créer le label pour la checkbox
            const label = document.createElement('label');
            label.setAttribute('for', inputId);
            label.className = 'option';
            label.textContent = 'Permettre aux autres utilisateurs de vous contacter à partir d\'un formulaire de contact personnel qui ne divulgue pas votre adresse de courriel. Il est à noter que certains utilisateurs privilégiés tels que les administrateurs du site restent en mesure de vous contacter même si vous décidez de désactiver cette fonctionnalité.';
            label.style.cssText = 'display: inline !important; visibility: visible !important; opacity: 1 !important; font-weight: normal !important; cursor: pointer !important; width: auto !important; pointer-events: auto !important; position: relative !important; z-index: 1 !important;';
            
            // Ajouter un event listener sur le label pour cocher/décocher la checkbox
            label.addEventListener('click', function(e) {
              e.preventDefault();
              e.stopPropagation();
              checkbox.checked = !checkbox.checked;
              checkbox.dispatchEvent(new Event('change', { bubbles: true }));
            });
            
            checkboxWrapper.appendChild(checkbox);
            checkboxWrapper.appendChild(label);
            
            // S'assurer que le wrapper et tous les parents ont pointer-events: auto
            checkboxWrapper.style.pointerEvents = 'auto';
            let currentElement = checkboxWrapper;
            while (currentElement && currentElement !== document.body) {
              currentElement.style.pointerEvents = 'auto';
              currentElement = currentElement.parentElement;
            }
            
            // Trouver le texte "contact" dans le form-item ou le conteneur details
            const details = formItem.closest('details') || formItem;
            const existingLabel = details.querySelector('summary, .fieldset-legend, label');
            
            if (existingLabel) {
              // Si c'est dans un details, ajouter après le summary
              if (details.tagName === 'DETAILS') {
                details.appendChild(checkboxWrapper);
              } else {
                existingLabel.parentNode.insertBefore(checkboxWrapper, existingLabel.nextSibling);
              }
            } else {
              // Chercher le texte "contact" ou "formulaire"
              const textNodes = [];
              const walker = document.createTreeWalker(
                formItem,
                NodeFilter.SHOW_TEXT,
                null,
                false
              );
              let node;
              while (node = walker.nextNode()) {
                const text = node.textContent.trim().toLowerCase();
                if (text && (text.includes('contact') || text.includes('formulaire'))) {
                  textNodes.push(node);
                }
              }
              
              if (textNodes.length > 0) {
                const parent = textNodes[0].parentNode;
                if (parent) {
                  const br = document.createElement('br');
                  parent.insertBefore(br, textNodes[0].nextSibling);
                  parent.insertBefore(checkboxWrapper, br.nextSibling);
                } else {
                  formItem.appendChild(checkboxWrapper);
                }
              } else {
                formItem.appendChild(checkboxWrapper);
              }
            }
          };
          
          // Chercher TOUS les form-items de manière large pour s'assurer de trouver les champs
          const allFormItemsWide = document.querySelectorAll('.form-item, .js-form-item');
          
          // Parcourir tous les form-items trouvés - traiter d'abord les champs de base (name, pass, mail)
          allFormItemsWide.forEach(function(formItem) {
            const classes = formItem.className || '';
            const text = formItem.textContent.toLowerCase();
            
            // Chercher par classe d'abord - traiter chaque champ indépendamment
            if ((classes.includes('form-item-name') || classes.includes('js-form-item-name')) && 
                !formItem.querySelector('#edit-name') && 
                !formItem.querySelector('input[name="name"]')) {
              injectInputInFormItem(formItem, 'name', 'text', 'username');
            }
            
            if ((classes.includes('form-item-pass') || classes.includes('js-form-item-pass')) && 
                !formItem.querySelector('#edit-pass') && 
                !formItem.querySelector('input[name="pass"]')) {
              injectInputInFormItem(formItem, 'pass', 'password', 'current-password');
            }
            
            if ((classes.includes('form-item-mail') || classes.includes('js-form-item-mail')) && 
                !formItem.querySelector('#edit-mail') && 
                !formItem.querySelector('input[name="mail"]')) {
              injectInputInFormItem(formItem, 'mail', 'email', 'email');
            }
            
            // Chercher aussi par texte si pas trouvé par classe (pour les champs de base uniquement)
            // Ne traiter que si aucun input n'existe déjà dans le form-item
            // ET que ce n'est pas une description ou un form-item pour contact/user_picture
            const hasAnyInput = formItem.querySelector('input[type="text"], input[type="password"], input[type="email"]');
            const isDescription = formItem.classList.contains('description') || formItem.closest('.description');
            const isContactOrPicture = classes.includes('form-item-contact') || classes.includes('form-item-user-picture') || 
                                       classes.includes('js-form-item-contact') || classes.includes('js-form-item-user-picture');
            
            if (!hasAnyInput && !isDescription && !isContactOrPicture) {
              if ((text.includes('nom') || text.includes('username') || text.includes('utilisateur')) && 
                  !formItem.querySelector('#edit-name') && 
                  !formItem.querySelector('input[name="name"]') &&
                  !formItem.querySelector('input[type="file"]') &&
                  !formItem.querySelector('input[type="checkbox"]')) {
                injectInputInFormItem(formItem, 'name', 'text', 'username');
              } else if ((text.includes('mot de passe') || text.includes('password')) && 
                         !formItem.querySelector('#edit-pass') && 
                         !formItem.querySelector('input[name="pass"]') &&
                         !formItem.querySelector('input[type="file"]') &&
                         !formItem.querySelector('input[type="checkbox"]')) {
                injectInputInFormItem(formItem, 'pass', 'password', 'current-password');
              } else if ((text.includes('email') || text.includes('e-mail') || text.includes('courriel')) && 
                         !formItem.querySelector('#edit-mail') && 
                         !formItem.querySelector('input[name="mail"]') &&
                         !formItem.querySelector('input[type="file"]') &&
                         !formItem.querySelector('input[type="checkbox"]')) {
                injectInputInFormItem(formItem, 'mail', 'email', 'email');
              }
            }
          });
          
          // Traiter les champs spéciaux (user_picture, contact) séparément
          allFormItems.forEach(function(formItem) {
            const classes = formItem.className || '';
            if ((classes.includes('form-item-user-picture') || classes.includes('js-form-item-user-picture') || classes.includes('user-picture') || classes.includes('user_picture')) &&
                !formItem.querySelector('input[type="file"]')) {
              injectFileUploadInFormItem(formItem, 'user_picture');
            }
            if ((classes.includes('form-item-contact') || classes.includes('js-form-item-contact')) &&
                !formItem.querySelector('input[type="checkbox"][name="contact"]')) {
              injectCheckboxInFormItem(formItem, 'contact');
            }
          });
          
          // Parcourir les éléments user_picture trouvés
          userPictureItems.forEach(function(item) {
            // Vérifier si c'est un form-item ou un conteneur
            if (item.classList.contains('form-item') || item.classList.contains('js-form-item') || item.querySelector('.form-item')) {
              const formItem = item.classList.contains('form-item') ? item : item.querySelector('.form-item') || item;
              if (!formItem.querySelector('input[type="file"]')) {
                injectFileUploadInFormItem(formItem, 'user_picture');
              }
            }
          });
          
          // Chercher aussi les form-items qui contiennent "image" ou "visage" ou "picture" dans leur texte mais n'ont pas de file input
          // MAIS seulement sur la page d'inscription pour éviter de casser les autres formulaires
          if (window.location.pathname.includes('/user/register')) {
            const allFormItemsForPicture = document.querySelectorAll('.form-item, .js-form-item');
            allFormItemsForPicture.forEach(function(formItem) {
              const text = formItem.textContent.toLowerCase();
              const classes = formItem.className || '';
              // Ne pas traiter les champs de base (name, pass, mail)
              if (!classes.includes('form-item-name') && !classes.includes('js-form-item-name') &&
                  !classes.includes('form-item-pass') && !classes.includes('js-form-item-pass') &&
                  !classes.includes('form-item-mail') && !classes.includes('js-form-item-mail')) {
                if ((text.includes('image') || text.includes('visage') || text.includes('picture') || text.includes('virtuel')) && 
                    !formItem.querySelector('input[type="file"]') &&
                    !formItem.querySelector('input[type="text"]') &&
                    !formItem.querySelector('input[type="email"]') &&
                    !formItem.querySelector('input[type="password"]')) {
                  // C'est probablement le champ user_picture
                  injectFileUploadInFormItem(formItem, 'user_picture');
                }
              }
            });
          }
          
          // Chercher aussi dans les éléments details pour le champ contact
          // Chercher tous les details qui pourraient contenir le champ contact
          const allDetails = document.querySelectorAll('details');
          allDetails.forEach(function(details) {
            const summary = details.querySelector('summary');
            if (summary && (summary.textContent.toLowerCase().includes('contact') || summary.textContent.toLowerCase().includes('formulaire'))) {
              // Chercher s'il y a déjà une checkbox contact
              let hasContactCheckbox = details.querySelector('input[type="checkbox"][name="contact"]');
              
              // Vérifier si le texte existe déjà pour éviter les doublons
              const contactText = 'Permettre aux autres utilisateurs de vous contacter à partir d\'un formulaire de contact personnel qui ne divulgue pas votre adresse de courriel. Il est à noter que certains utilisateurs privilégiés tels que les administrateurs du site restent en mesure de vous contacter même si vous décidez de désactiver cette fonctionnalité.';
              const occurrences = (details.textContent.match(new RegExp(contactText.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'), 'g')) || []).length;
              
              // Vérifier aussi dans tout le document
              const allLabels = document.querySelectorAll('label');
              let textExists = false;
              allLabels.forEach(function(label) {
                if (label.textContent.trim() === contactText.trim()) {
                  textExists = true;
                }
              });
              
              // Si pas de checkbox, chercher un input text avec le nom contact et le remplacer
              const contactTextInput = details.querySelector('input[type="text"][name="contact"], input[name*="contact"]');
              if (contactTextInput && contactTextInput.type === 'text' && occurrences === 0 && !textExists) {
                // Remplacer l'input text par une checkbox
                const checkbox = document.createElement('input');
                checkbox.type = 'checkbox';
                checkbox.name = 'contact';
                checkbox.id = contactTextInput.id || 'edit-contact';
                checkbox.className = 'form-checkbox';
                checkbox.setAttribute('data-drupal-selector', 'edit-contact');
                checkbox.value = '1';
                checkbox.style.cssText = 'display: inline-block !important; visibility: visible !important; opacity: 1 !important; margin-right: 0.5rem !important; width: auto !important; height: auto !important; cursor: pointer !important; position: relative !important; z-index: 9999 !important; pointer-events: auto !important;';
                checkbox.disabled = false;
                checkbox.readOnly = false;
                
                // Ajouter un event listener pour s'assurer que la checkbox fonctionne
                checkbox.addEventListener('click', function(e) {
                  e.stopPropagation();
                }, true);
                
                // Créer un wrapper pour la checkbox et le label
                const checkboxWrapper = document.createElement('div');
                checkboxWrapper.className = 'js-form-item form-item js-form-type-checkbox form-type-checkbox form-item-contact js-form-item-contact';
                checkboxWrapper.style.cssText = 'display: block !important; visibility: visible !important; opacity: 1 !important; pointer-events: auto !important;';
                
                // Créer le label
                const label = document.createElement('label');
                label.setAttribute('for', checkbox.id);
                label.className = 'option';
                label.textContent = 'Permettre aux autres utilisateurs de vous contacter à partir d\'un formulaire de contact personnel qui ne divulgue pas votre adresse de courriel. Il est à noter que certains utilisateurs privilégiés tels que les administrateurs du site restent en mesure de vous contacter même si vous décidez de désactiver cette fonctionnalité.';
                label.style.cssText = 'display: inline !important; visibility: visible !important; opacity: 1 !important; font-weight: normal !important; cursor: pointer !important; width: auto !important; pointer-events: auto !important; position: relative !important; z-index: 1 !important;';
                
                // Ajouter un event listener sur le label pour cocher/décocher la checkbox
                label.addEventListener('click', function(e) {
                  e.preventDefault();
                  e.stopPropagation();
                  checkbox.checked = !checkbox.checked;
                  checkbox.dispatchEvent(new Event('change', { bubbles: true }));
                });
                
                checkboxWrapper.appendChild(checkbox);
                checkboxWrapper.appendChild(label);
                
                // Remplacer l'input text par le wrapper avec la checkbox
                contactTextInput.parentNode.replaceChild(checkboxWrapper, contactTextInput);
                
                // S'assurer que tous les parents ont pointer-events: auto
                let currentElement = checkboxWrapper;
                while (currentElement && currentElement !== document.body) {
                  currentElement.style.pointerEvents = 'auto';
                  currentElement = currentElement.parentElement;
                }
              } else if (!hasContactCheckbox && occurrences === 0 && !textExists) {
                // Créer un form-item s'il n'existe pas
                const formItem = document.createElement('div');
                formItem.className = 'form-item js-form-item form-type-checkbox js-form-type-checkbox form-item-contact js-form-item-contact';
                details.appendChild(formItem);
                injectCheckboxInFormItem(formItem, 'contact');
              }
            }
          });
          
          // Chercher aussi les form-items qui contiennent "contact" dans leur texte mais n'ont pas de checkbox
          const allFormItemsWithContact = document.querySelectorAll('.form-item, .js-form-item');
          allFormItemsWithContact.forEach(function(formItem) {
            const text = formItem.textContent.toLowerCase();
            if (text.includes('formulaire de contact') || text.includes('contact personnel')) {
              const hasCheckbox = formItem.querySelector('input[type="checkbox"][name="contact"]');
              const hasTextInput = formItem.querySelector('input[type="text"][name="contact"], input[type="text"][name*="contact"]');
              
              // Vérifier si le texte existe déjà pour éviter les doublons
              const contactText = 'Permettre aux autres utilisateurs de vous contacter à partir d\'un formulaire de contact personnel qui ne divulgue pas votre adresse de courriel. Il est à noter que certains utilisateurs privilégiés tels que les administrateurs du site restent en mesure de vous contacter même si vous décidez de désactiver cette fonctionnalité.';
              const occurrences = (formItem.textContent.match(new RegExp(contactText.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'), 'g')) || []).length;
              
              // Vérifier aussi dans tout le document
              const allLabels = document.querySelectorAll('label');
              let textExists = false;
              allLabels.forEach(function(label) {
                if (label.textContent.trim() === contactText.trim()) {
                  textExists = true;
                }
              });
              
              if (hasTextInput && !hasCheckbox && occurrences === 0 && !textExists) {
                // Remplacer l'input text par une checkbox
                const checkbox = document.createElement('input');
                checkbox.type = 'checkbox';
                checkbox.name = 'contact';
                checkbox.id = hasTextInput.id || 'edit-contact';
                checkbox.className = 'form-checkbox';
                checkbox.setAttribute('data-drupal-selector', 'edit-contact');
                checkbox.value = '1';
                checkbox.style.cssText = 'display: inline-block !important; visibility: visible !important; opacity: 1 !important; margin-right: 0.5rem !important; width: auto !important; height: auto !important; cursor: pointer !important; position: relative !important; z-index: 9999 !important; pointer-events: auto !important;';
                checkbox.disabled = false;
                checkbox.readOnly = false;
                
                // Ajouter un event listener pour s'assurer que la checkbox fonctionne
                checkbox.addEventListener('click', function(e) {
                  e.stopPropagation();
                }, true);
                
                // Créer un conteneur pour la checkbox et le label
                const checkboxWrapper = document.createElement('div');
                checkboxWrapper.className = 'js-form-item form-item js-form-type-checkbox form-type-checkbox form-item-contact js-form-item-contact';
                checkboxWrapper.style.cssText = 'display: block !important; visibility: visible !important; opacity: 1 !important; pointer-events: auto !important;';
                
                const label = document.createElement('label');
                label.setAttribute('for', checkbox.id);
                label.className = 'option';
                label.textContent = 'Permettre aux autres utilisateurs de vous contacter à partir d\'un formulaire de contact personnel qui ne divulgue pas votre adresse de courriel. Il est à noter que certains utilisateurs privilégiés tels que les administrateurs du site restent en mesure de vous contacter même si vous décidez de désactiver cette fonctionnalité.';
                label.style.cssText = 'display: inline !important; visibility: visible !important; opacity: 1 !important; font-weight: normal !important; cursor: pointer !important; width: auto !important; pointer-events: auto !important; position: relative !important; z-index: 1 !important;';
                
                // Ajouter un event listener sur le label pour cocher/décocher la checkbox
                label.addEventListener('click', function(e) {
                  e.preventDefault();
                  e.stopPropagation();
                  checkbox.checked = !checkbox.checked;
                  checkbox.dispatchEvent(new Event('change', { bubbles: true }));
                });
                
                checkboxWrapper.appendChild(checkbox);
                checkboxWrapper.appendChild(label);
                
                // S'assurer que tous les parents ont pointer-events: auto
                let currentElement = checkboxWrapper;
                while (currentElement && currentElement !== document.body) {
                  currentElement.style.pointerEvents = 'auto';
                  currentElement = currentElement.parentElement;
                }
                
                // Remplacer l'input text par le wrapper avec la checkbox
                hasTextInput.parentNode.replaceChild(checkboxWrapper, hasTextInput);
              } else if (!hasCheckbox && occurrences === 0 && !textExists) {
                injectCheckboxInFormItem(formItem, 'contact');
              }
            }
          });
          
          // Chercher aussi dans tout le document les inputs text avec name="contact" et les remplacer
          const allContactTextInputs = document.querySelectorAll('input[type="text"][name="contact"], input[type="text"][name*="contact"]');
          allContactTextInputs.forEach(function(textInput) {
            // Vérifier que c'est bien un input text et qu'il n'y a pas déjà une checkbox
            if (textInput.type === 'text') {
              const parentFormItem = textInput.closest('.form-item, .js-form-item');
              const hasCheckbox = parentFormItem ? parentFormItem.querySelector('input[type="checkbox"][name="contact"]') : document.querySelector('input[type="checkbox"][name="contact"]');
              
              // Vérifier aussi si le texte existe déjà
              const contactText = 'Permettre aux autres utilisateurs de vous contacter à partir d\'un formulaire de contact personnel qui ne divulgue pas votre adresse de courriel. Il est à noter que certains utilisateurs privilégiés tels que les administrateurs du site restent en mesure de vous contacter même si vous décidez de désactiver cette fonctionnalité.';
              const parentElement = parentFormItem || textInput.parentElement;
              const occurrences = parentElement ? (parentElement.textContent.match(new RegExp(contactText.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'), 'g')) || []).length : 0;
              
              // Vérifier aussi dans tout le document
              const allLabels = document.querySelectorAll('label');
              let textExists = false;
              allLabels.forEach(function(label) {
                if (label.textContent.trim() === contactText.trim()) {
                  textExists = true;
                }
              });
              
              if (!hasCheckbox && occurrences === 0 && !textExists) {
                const checkbox = document.createElement('input');
                checkbox.type = 'checkbox';
                checkbox.name = 'contact';
                checkbox.id = textInput.id || 'edit-contact';
                checkbox.className = 'form-checkbox';
                checkbox.setAttribute('data-drupal-selector', 'edit-contact');
                checkbox.value = '1';
                checkbox.style.cssText = 'display: inline-block !important; visibility: visible !important; opacity: 1 !important; margin-right: 0.5rem !important; width: auto !important; height: auto !important; cursor: pointer !important; position: relative !important; z-index: 9999 !important; pointer-events: auto !important;';
                checkbox.disabled = false;
                checkbox.readOnly = false;
                
                // Ajouter un event listener pour s'assurer que la checkbox fonctionne
                checkbox.addEventListener('click', function(e) {
                  e.stopPropagation();
                }, true);
                
                const checkboxWrapper = document.createElement('div');
                checkboxWrapper.className = 'js-form-item form-item js-form-type-checkbox form-type-checkbox form-item-contact js-form-item-contact';
                checkboxWrapper.style.cssText = 'display: block !important; visibility: visible !important; opacity: 1 !important; pointer-events: auto !important;';
                
                const label = document.createElement('label');
                label.setAttribute('for', checkbox.id);
                label.className = 'option';
                label.textContent = 'Permettre aux autres utilisateurs de vous contacter à partir d\'un formulaire de contact personnel qui ne divulgue pas votre adresse de courriel. Il est à noter que certains utilisateurs privilégiés tels que les administrateurs du site restent en mesure de vous contacter même si vous décidez de désactiver cette fonctionnalité.';
                label.style.cssText = 'display: inline !important; visibility: visible !important; opacity: 1 !important; font-weight: normal !important; cursor: pointer !important; width: auto !important; pointer-events: auto !important; position: relative !important; z-index: 1 !important;';
                
                // Ajouter un event listener sur le label pour cocher/décocher la checkbox
                label.addEventListener('click', function(e) {
                  e.preventDefault();
                  e.stopPropagation();
                  checkbox.checked = !checkbox.checked;
                  checkbox.dispatchEvent(new Event('change', { bubbles: true }));
                });
                
                checkboxWrapper.appendChild(checkbox);
                checkboxWrapper.appendChild(label);
                
                // Remplacer l'input text par le wrapper avec la checkbox
                textInput.parentNode.replaceChild(checkboxWrapper, textInput);
                
                // S'assurer que tous les parents ont pointer-events: auto
                let currentElement = checkboxWrapper;
                while (currentElement && currentElement !== document.body) {
                  currentElement.style.pointerEvents = 'auto';
                  currentElement = currentElement.parentElement;
                }
              }
            }
          });
          
          // S'assurer que les checkboxes contact existantes ont les bons styles et sont cliquables
          const existingContactCheckboxes = document.querySelectorAll('input[type="checkbox"][name="contact"]');
          existingContactCheckboxes.forEach(function(checkbox) {
            // Forcer les styles pour que la checkbox soit cliquable
            checkbox.style.setProperty('display', 'inline-block', 'important');
            checkbox.style.setProperty('visibility', 'visible', 'important');
            checkbox.style.setProperty('opacity', '1', 'important');
            checkbox.style.setProperty('margin-right', '0.5rem', 'important');
            checkbox.style.setProperty('width', 'auto', 'important');
            checkbox.style.setProperty('height', 'auto', 'important');
            checkbox.style.setProperty('cursor', 'pointer', 'important');
            checkbox.style.setProperty('position', 'relative', 'important');
            checkbox.style.setProperty('z-index', '99999', 'important');
            checkbox.style.setProperty('pointer-events', 'auto', 'important');
            checkbox.disabled = false;
            checkbox.readOnly = false;
            checkbox.removeAttribute('disabled');
            checkbox.removeAttribute('readonly');
            
            // S'assurer qu'aucun élément n'est par-dessus la checkbox en vérifiant tous les parents
            let currentElement = checkbox;
            while (currentElement && currentElement !== document.body) {
              currentElement.style.setProperty('pointer-events', 'auto', 'important');
              if (currentElement === checkbox) {
                currentElement.style.setProperty('z-index', '99999', 'important');
              }
              // Vérifier les enfants pour s'assurer qu'aucun n'est par-dessus
              const children = currentElement.children;
              for (let i = 0; i < children.length; i++) {
                if (children[i] !== checkbox) {
                  const childStyle = window.getComputedStyle(children[i]);
                  if (childStyle.position === 'absolute' || childStyle.position === 'fixed') {
                    const zIndex = parseInt(childStyle.zIndex) || 0;
                    if (zIndex > 99998) {
                      children[i].style.setProperty('z-index', '1', 'important');
                    }
                  }
                }
              }
              currentElement = currentElement.parentElement;
            }
            
            // S'assurer que le label associé a aussi les bons styles
            const label = document.querySelector('label[for="' + checkbox.id + '"]');
            if (label) {
              label.style.setProperty('display', 'inline', 'important');
              label.style.setProperty('visibility', 'visible', 'important');
              label.style.setProperty('opacity', '1', 'important');
              label.style.setProperty('font-weight', 'normal', 'important');
              label.style.setProperty('cursor', 'pointer', 'important');
              label.style.setProperty('width', 'auto', 'important');
              label.style.setProperty('pointer-events', 'auto', 'important');
              label.style.setProperty('position', 'relative', 'important');
              label.style.setProperty('z-index', '1', 'important');
              
              // Ajouter un event listener sur le label pour cocher/décocher la checkbox
              label.onclick = function(e) {
                e.preventDefault();
                e.stopPropagation();
                checkbox.checked = !checkbox.checked;
                const changeEvent = new Event('change', { bubbles: true, cancelable: true });
                checkbox.dispatchEvent(changeEvent);
                return false;
              };
            }
            
            // Ajouter un event listener direct sur la checkbox pour s'assurer qu'elle fonctionne
            checkbox.onclick = function(e) {
              e.stopPropagation();
              return true;
            };
            
            checkbox.onchange = function(e) {
              e.stopPropagation();
              return true;
            };
          });
          
          // Supprimer les doublons de texte pour le champ contact
          const contactText = 'Permettre aux autres utilisateurs de vous contacter à partir d\'un formulaire de contact personnel qui ne divulgue pas votre adresse de courriel. Il est à noter que certains utilisateurs privilégiés tels que les administrateurs du site restent en mesure de vous contacter même si vous décidez de désactiver cette fonctionnalité.';
          const contactTextNormalized = contactText.trim().replace(/\s+/g, ' ');
          const contactTextStart = contactTextNormalized.substring(0, 50);
          
          // Supprimer TOUTES les descriptions qui contiennent ce texte (on garde seulement le label)
          const allDescriptions = document.querySelectorAll('.description');
          allDescriptions.forEach(function(description) {
            const descText = description.textContent.trim().replace(/\s+/g, ' ');
            // Vérifier si la description contient le texte de contact
            if (descText.includes(contactTextStart) || descText === contactTextNormalized) {
              // Vérifier aussi si c'est dans un form-item-contact
              const parentFormItem = description.closest('.form-item-contact, .js-form-item-contact');
              if (parentFormItem) {
                // C'est une description avec le texte de contact dans le form-item-contact, la supprimer
                description.remove();
              }
            }
          });
          
          // Chercher tous les labels qui contiennent ce texte
          const allLabels = document.querySelectorAll('label');
          const labelsWithContactText = [];
          allLabels.forEach(function(label) {
            const labelText = label.textContent.trim().replace(/\s+/g, ' ');
            if (labelText === contactTextNormalized || labelText.includes(contactTextStart)) {
              labelsWithContactText.push(label);
            }
          });
          
          // Garder seulement le premier label, supprimer les autres
          if (labelsWithContactText.length > 1) {
            for (let i = 1; i < labelsWithContactText.length; i++) {
              labelsWithContactText[i].remove();
            }
          }
          
          // Supprimer aussi les inputs text qui ont été injectés par erreur dans les descriptions
          const allDescriptionsWithInputs = document.querySelectorAll('.description input[type="text"], .description input[type="email"], .description input[type="password"]');
          allDescriptionsWithInputs.forEach(function(input) {
            input.remove();
          });
          
          // Supprimer aussi les descriptions en double dans les form-items contact
          const contactFormItems = document.querySelectorAll('.form-item-contact, .js-form-item-contact');
          contactFormItems.forEach(function(formItem) {
            const descriptions = formItem.querySelectorAll('.description');
            let foundFirst = false;
            descriptions.forEach(function(desc) {
              const descText = desc.textContent.trim().replace(/\s+/g, ' ');
              if (descText.includes(contactTextStart) || descText === contactTextNormalized) {
                if (foundFirst) {
                  desc.remove();
                } else {
                  foundFirst = true;
                }
              }
            });
          });
          
          // S'assurer que le bouton submit existe et qu'il est dans un formulaire
          const actionsDiv = document.querySelector('#edit-actions, .form-actions');
          if (actionsDiv && !actionsDiv.querySelector('input[type="submit"], button[type="submit"]')) {
            // Trouver le formulaire parent (peut être celui qu'on vient de créer)
            const parentForm = actionsDiv.closest('form') || form;
            if (parentForm) {
              // Déterminer le texte du bouton selon la page
              let buttonText = 'Se connecter';
              const currentPath = window.location.pathname;
              if (currentPath.includes('/user/register')) {
                buttonText = 'Créer un nouveau compte';
              } else if (currentPath.includes('/user/password')) {
                buttonText = 'Envoyer';
              }
              
              // Créer le bouton submit
              const submitButton = document.createElement('input');
              submitButton.type = 'submit';
              submitButton.value = buttonText;
              submitButton.className = 'form-submit button button--primary';
              submitButton.id = 'edit-submit';
              submitButton.name = 'op';
              submitButton.setAttribute('data-drupal-selector', 'edit-submit');
              submitButton.style.cssText = 'display: inline-block !important; visibility: visible !important; opacity: 1 !important; padding: 0.75rem 2rem !important; background-color: #b80000 !important; color: white !important; border: none !important; border-radius: 4px !important; font-weight: 600 !important; font-size: 1rem !important; cursor: pointer !important; margin-top: 1rem !important;';
              actionsDiv.appendChild(submitButton);
            }
          }
          
          // S'assurer que le formulaire d'inscription a l'enctype pour l'upload
          const finalForm = document.querySelector('form#user-login-form, form#user-register-form, form#user-pass') || form;
          if (finalForm && currentPath.includes('/user/register') && !finalForm.getAttribute('enctype')) {
            finalForm.setAttribute('enctype', 'multipart/form-data');
          }
          
          // S'assurer que tous les champs cachés nécessaires sont présents dans le formulaire
          if (finalForm) {
            // Vérifier si les champs cachés existent déjà dans le formulaire
            const formIdInForm = finalForm.querySelector('input[name="form_id"]');
            const formBuildIdInForm = finalForm.querySelector('input[name="form_build_id"]');
            const formTokenInForm = finalForm.querySelector('input[name="form_token"]');
            
            // Si form_build_id n'existe pas, essayer de le récupérer depuis le document
            if (!formBuildIdInForm) {
              // Chercher dans tout le document (peut être en dehors du formulaire)
              const buildIdAnywhere = document.querySelector('input[name="form_build_id"]');
              if (buildIdAnywhere) {
                // Copier le champ dans le formulaire
                const buildIdInput = document.createElement('input');
                buildIdInput.type = 'hidden';
                buildIdInput.name = 'form_build_id';
                buildIdInput.value = buildIdAnywhere.value;
                buildIdInput.id = buildIdAnywhere.id;
                buildIdInput.setAttribute('autocomplete', 'off');
                finalForm.insertBefore(buildIdInput, finalForm.firstChild);
              } else {
                // Si toujours pas trouvé, créer un nouveau (peut ne pas fonctionner avec Drupal)
                const buildIdInput = document.createElement('input');
                buildIdInput.type = 'hidden';
                buildIdInput.name = 'form_build_id';
                buildIdInput.value = 'form-' + Math.random().toString(36).substring(2, 15) + Math.random().toString(36).substring(2, 15);
                buildIdInput.id = 'edit-form-build-id';
                buildIdInput.setAttribute('autocomplete', 'off');
                finalForm.insertBefore(buildIdInput, finalForm.firstChild);
              }
            }
            
            // Si form_id n'existe pas, l'ajouter
            if (!formIdInForm) {
              let formIdValue = 'user_login_form';
              const currentPath = window.location.pathname;
              if (currentPath.includes('/user/register')) {
                formIdValue = 'user_register_form';
              } else if (currentPath.includes('/user/password')) {
                formIdValue = 'user_pass';
              }
              
              const formIdAnywhere = document.querySelector('input[name="form_id"]');
              const formIdInput = document.createElement('input');
              formIdInput.type = 'hidden';
              formIdInput.name = 'form_id';
              formIdInput.value = formIdAnywhere ? formIdAnywhere.value : formIdValue;
              formIdInput.id = formIdAnywhere ? formIdAnywhere.id : 'edit-' + formIdValue;
              finalForm.insertBefore(formIdInput, finalForm.firstChild);
            }
            
            // Si form_token n'existe pas mais est présent ailleurs, l'ajouter
            if (!formTokenInForm) {
              const tokenAnywhere = document.querySelector('input[name="form_token"]');
              if (tokenAnywhere) {
                const tokenInput = document.createElement('input');
                tokenInput.type = 'hidden';
                tokenInput.name = 'form_token';
                tokenInput.value = tokenAnywhere.value;
                tokenInput.id = tokenAnywhere.id;
                finalForm.insertBefore(tokenInput, finalForm.firstChild);
              }
            }
          }
        },

        /**
         * Force la toolbar à rester en mode vertical.
         */
        forceVerticalToolbar: function (context) {
          // Laisser Drupal gérer la toolbar avec ses styles par défaut
          // Cette fonction est conservée mais ne fait rien pour ne pas interférer
        },

    /**
     * Charge le Breaking News via AJAX.
     */
    loadBreakingNews: function (context) {
      const breakingNewsContainer = context.querySelector('.breaking-news-container');
      if (!breakingNewsContainer) {
        return;
      }

      fetch('/api/breaking-news')
        .then(response => response.json())
        .then(data => {
          if (data.title) {
            breakingNewsContainer.innerHTML = `
              <div class="breaking-news">
                <a href="${data.url}">${data.title}</a>
              </div>
            `;
          }
        })
        .catch(error => {
          console.error('Error loading breaking news:', error);
        });
    },

    /**
     * Initialise le carrousel d'articles.
     */
    initCarousel: function (context) {
      const carousel = context.querySelector('.articles-carousel');
      if (!carousel) {
        return;
      }

      // Implémentation simple du carrousel
      // Pour une version plus avancée, utiliser une librairie comme Swiper.js
      let currentIndex = 0;
      const items = carousel.querySelectorAll('.carousel-item');
      const totalItems = items.length;

      if (totalItems <= 1) {
        return;
      }

      const nextButton = carousel.querySelector('.carousel-next');
      const prevButton = carousel.querySelector('.carousel-prev');

      if (nextButton) {
        nextButton.addEventListener('click', () => {
          currentIndex = (currentIndex + 1) % totalItems;
          this.updateCarousel(carousel, currentIndex, items);
        });
      }

      if (prevButton) {
        prevButton.addEventListener('click', () => {
          currentIndex = (currentIndex - 1 + totalItems) % totalItems;
          this.updateCarousel(carousel, currentIndex, items);
        });
      }

      // Auto-play (optionnel)
      setInterval(() => {
        currentIndex = (currentIndex + 1) % totalItems;
        this.updateCarousel(carousel, currentIndex, items);
      }, 5000);
    },

    /**
     * Met à jour l'affichage du carrousel.
     */
    updateCarousel: function (carousel, index, items) {
      items.forEach((item, i) => {
        item.classList.toggle('active', i === index);
      });
    },

    /**
     * Initialise le menu mobile.
     */
    initMobileMenu: function (context) {
      const menuToggle = context.querySelector('.menu-toggle');
      const siteHeader = context.querySelector('.site-header');
      const mainNav = context.querySelector('.main-navigation');
      const menuBlock = context.querySelector('.menu.menu--main');

      // Fonction pour cacher le menu sur mobile
      const hideMenuOnMobile = function() {
        if (window.innerWidth <= 767) {
          if (mainNav) {
            mainNav.classList.add('mobile-menu-hidden');
            mainNav.style.display = 'none';
            mainNav.style.visibility = 'hidden';
            mainNav.style.opacity = '0';
            mainNav.style.height = '0';
            mainNav.style.width = '0';
            mainNav.style.overflow = 'hidden';
            mainNav.style.position = 'absolute';
            mainNav.style.left = '-9999px';
            mainNav.style.pointerEvents = 'none';
          }
          if (menuBlock) {
            menuBlock.style.display = 'none';
            menuBlock.style.visibility = 'hidden';
            menuBlock.style.opacity = '0';
            menuBlock.style.height = '0';
            menuBlock.style.width = '0';
            menuBlock.style.overflow = 'hidden';
            menuBlock.style.position = 'absolute';
            menuBlock.style.left = '-9999px';
            menuBlock.style.pointerEvents = 'none';
          }
          // Fermer le menu s'il est ouvert
          if (siteHeader && siteHeader.classList.contains('menu-open')) {
            siteHeader.classList.remove('menu-open');
            if (menuToggle) {
              menuToggle.setAttribute('aria-expanded', 'false');
              menuToggle.classList.remove('active');
              const icon = menuToggle.querySelector('.menu-toggle-icon svg');
              if (icon) {
                icon.innerHTML = '<path d="M3 12H21M3 6H21M3 18H21" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>';
              }
            }
          }
        } else {
          // Sur desktop, réinitialiser les styles et afficher le menu
          if (mainNav) {
            mainNav.classList.remove('mobile-menu-hidden');
            mainNav.style.display = '';
            mainNav.style.visibility = '';
            mainNav.style.opacity = '';
            mainNav.style.height = '';
            mainNav.style.width = '';
            mainNav.style.overflow = '';
            mainNav.style.position = '';
            mainNav.style.left = '';
            mainNav.style.pointerEvents = '';
          }
          if (menuBlock) {
            menuBlock.style.display = '';
            menuBlock.style.visibility = '';
            menuBlock.style.opacity = '';
            menuBlock.style.height = '';
            menuBlock.style.width = '';
            menuBlock.style.overflow = '';
            menuBlock.style.position = '';
            menuBlock.style.left = '';
            menuBlock.style.pointerEvents = '';
          }
          // Fermer le menu s'il est ouvert
          if (siteHeader && siteHeader.classList.contains('menu-open')) {
            siteHeader.classList.remove('menu-open');
            if (menuToggle) {
              menuToggle.setAttribute('aria-expanded', 'false');
              menuToggle.classList.remove('active');
              const icon = menuToggle.querySelector('.menu-toggle-icon svg');
              if (icon) {
                icon.innerHTML = '<path d="M3 12H21M3 6H21M3 18H21" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>';
              }
            }
          }
        }
      };

      // Cacher le menu au chargement si on est sur mobile
      hideMenuOnMobile();

      // Écouter les changements de taille de fenêtre
      let resizeTimeout;
      window.addEventListener('resize', function() {
        clearTimeout(resizeTimeout);
        resizeTimeout = setTimeout(function() {
          hideMenuOnMobile();
        }, 100);
      });

      if (menuToggle && siteHeader) {
        menuToggle.addEventListener('click', function (e) {
          e.preventDefault();
          e.stopPropagation();
          siteHeader.classList.toggle('menu-open');
          const isOpen = siteHeader.classList.contains('menu-open');
          menuToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
          menuToggle.classList.toggle('active', isOpen);
          
          // Gérer la classe mobile-menu-hidden et les styles inline
          const menuBlock = siteHeader.querySelector('.menu.menu--main');
          if (mainNav) {
            if (isOpen) {
              mainNav.classList.remove('mobile-menu-hidden');
              mainNav.style.display = 'block';
              mainNav.style.visibility = 'visible';
              mainNav.style.opacity = '1';
              mainNav.style.height = 'auto';
              mainNav.style.width = '100%';
              mainNav.style.overflow = 'visible';
              mainNav.style.position = 'relative';
              mainNav.style.left = '0';
              mainNav.style.pointerEvents = 'auto';
            } else {
              mainNav.classList.add('mobile-menu-hidden');
              mainNav.style.display = 'none';
              mainNav.style.visibility = 'hidden';
              mainNav.style.opacity = '0';
              mainNav.style.height = '0';
              mainNav.style.width = '0';
              mainNav.style.overflow = 'hidden';
              mainNav.style.position = 'absolute';
              mainNav.style.left = '-9999px';
              mainNav.style.pointerEvents = 'none';
            }
          }
          if (menuBlock) {
            if (isOpen) {
              menuBlock.style.display = 'block';
              menuBlock.style.visibility = 'visible';
              menuBlock.style.opacity = '1';
              menuBlock.style.height = 'auto';
              menuBlock.style.width = '100%';
              menuBlock.style.overflow = 'visible';
              menuBlock.style.position = 'relative';
              menuBlock.style.left = '0';
              menuBlock.style.pointerEvents = 'auto';
            } else {
              menuBlock.style.display = 'none';
              menuBlock.style.visibility = 'hidden';
              menuBlock.style.opacity = '0';
              menuBlock.style.height = '0';
              menuBlock.style.width = '0';
              menuBlock.style.overflow = 'hidden';
              menuBlock.style.position = 'absolute';
              menuBlock.style.left = '-9999px';
              menuBlock.style.pointerEvents = 'none';
            }
          }
          
          // Changer l'icône du bouton
          const icon = menuToggle.querySelector('.menu-toggle-icon svg');
          if (icon) {
            if (isOpen) {
              // Icône X (fermer)
              icon.innerHTML = '<path d="M18 6L6 18M6 6L18 18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>';
            } else {
              // Icône hamburger (ouvrir)
              icon.innerHTML = '<path d="M3 12H21M3 6H21M3 18H21" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>';
            }
          }
        });

        // Fermer le menu quand on clique en dehors
        document.addEventListener('click', function (e) {
          if (siteHeader.classList.contains('menu-open')) {
            const mainNav = siteHeader.querySelector('.main-navigation');
            const headerRight = siteHeader.querySelector('.header-right-section');
            const clickedInsideMenu = (mainNav && mainNav.contains(e.target)) || 
                                     (headerRight && headerRight.contains(e.target)) ||
                                     menuToggle.contains(e.target);
            
            if (!clickedInsideMenu) {
              siteHeader.classList.remove('menu-open');
              menuToggle.setAttribute('aria-expanded', 'false');
              menuToggle.classList.remove('active');
              if (mainNav) {
                mainNav.classList.add('mobile-menu-hidden');
                mainNav.style.display = 'none';
                mainNav.style.visibility = 'hidden';
                mainNav.style.opacity = '0';
                mainNav.style.height = '0';
                mainNav.style.width = '0';
                mainNav.style.overflow = 'hidden';
                mainNav.style.position = 'absolute';
                mainNav.style.left = '-9999px';
                mainNav.style.pointerEvents = 'none';
              }
              const icon = menuToggle.querySelector('.menu-toggle-icon svg');
              if (icon) {
                icon.innerHTML = '<path d="M3 12H21M3 6H21M3 18H21" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>';
              }
            }
          }
        });

        // Fermer le menu quand on clique sur un lien de navigation
        if (mainNav) {
          const menuLinks = mainNav.querySelectorAll('a');
          menuLinks.forEach(link => {
            link.addEventListener('click', function () {
              siteHeader.classList.remove('menu-open');
              menuToggle.setAttribute('aria-expanded', 'false');
              menuToggle.classList.remove('active');
              mainNav.classList.add('mobile-menu-hidden');
              mainNav.style.display = 'none';
              mainNav.style.visibility = 'hidden';
              mainNav.style.opacity = '0';
              mainNav.style.height = '0';
              mainNav.style.width = '0';
              mainNav.style.overflow = 'hidden';
              mainNav.style.position = 'absolute';
              mainNav.style.left = '-9999px';
              mainNav.style.pointerEvents = 'none';
              const icon = menuToggle.querySelector('.menu-toggle-icon svg');
              if (icon) {
                icon.innerHTML = '<path d="M3 12H21M3 6H21M3 18H21" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>';
              }
            });
          });
        }
      }
    },

    /**
     * Gère le scroll pour la navigation sticky.
     * Ajoute un padding-top au main-content quand le header est collé.
     */
    handleStickyNav: function (context) {
      const siteHeader = context.querySelector('.site-header');
      const mainContent = context.querySelector('.main-content');
      
      if (!siteHeader || !mainContent) {
        return;
      }

      // Calculer la hauteur de la toolbar
      const getToolbarHeight = () => {
        const toolbar = document.querySelector('#toolbar-bar');
        if (toolbar && toolbar.offsetHeight > 0) {
          return toolbar.offsetHeight;
        }
        return 0;
      };

      // Calculer la hauteur totale (header + toolbar si présente)
      const getTotalHeight = () => {
        const toolbarHeight = getToolbarHeight();
        return siteHeader.offsetHeight + toolbarHeight;
      };

      // Ajuster le top du header sticky pour qu'il soit sous la toolbar
      const adjustHeaderTop = () => {
        const toolbarHeight = getToolbarHeight();
        if (toolbarHeight > 0) {
          siteHeader.style.top = toolbarHeight + 'px';
        } else {
          siteHeader.style.top = '0';
        }
      };

      // Fonction pour vérifier si on a scrollé
      const checkScroll = () => {
        const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
        
        // Si on a scrollé au-delà de la position initiale du header
        if (scrollTop > 0) {
          document.body.classList.add('header-sticky-active');
        } else {
          document.body.classList.remove('header-sticky-active');
        }
      };

      // Ajuster le top du header au chargement
      adjustHeaderTop();

      // Vérifier au chargement
      checkScroll();

      // Écouter le scroll avec throttling pour performance
      let ticking = false;
      window.addEventListener('scroll', () => {
        if (!ticking) {
          window.requestAnimationFrame(() => {
            checkScroll();
            ticking = false;
          });
          ticking = true;
        }
      });

      // Écouter aussi le resize pour recalculer la hauteur
      window.addEventListener('resize', () => {
        adjustHeaderTop();
      });

      // Observer les changements de la toolbar (ouverture/fermeture)
      const toolbarObserver = new MutationObserver(() => {
        adjustHeaderTop();
      });

      const toolbar = document.querySelector('#toolbar-bar');
      if (toolbar) {
        toolbarObserver.observe(toolbar, {
          attributes: true,
          attributeFilter: ['class', 'style'],
          childList: true,
          subtree: true
        });
      }

      // Observer aussi le body pour les changements de classe toolbar
      const bodyObserver = new MutationObserver(() => {
        adjustHeaderTop();
      });

      bodyObserver.observe(document.body, {
        attributes: true,
        attributeFilter: ['class']
      });
    },

    /**
     * Corrige l'affichage de la toolbar utilisateur sur les pages front-end.
     */
    fixToolbarUser: function (context) {
      // Trouver l'élément de la toolbar utilisateur
      const userTab = context.querySelector('.toolbar-icon-user');
      if (!userTab) {
        return;
      }

      // Trouver le lien parent
      const userTabLink = userTab.closest('a');
      if (!userTabLink) {
        return;
      }

      // Récupérer le nom d'utilisateur depuis les settings
      let username = null;
      if (typeof drupalSettings !== 'undefined' && drupalSettings.user && drupalSettings.user.name) {
        username = drupalSettings.user.name;
      }

      // Vérifier si le texte est vide ou contient juste un espace/placeholder
      const tabText = userTabLink.textContent.trim();
      const isEmpty = !tabText || tabText === '' || tabText === '\u00A0' || tabText === '&nbsp;' || tabText.length === 0;
      
      if (isEmpty && username) {
        // Remplacer le texte vide par le nom d'utilisateur
        userTabLink.textContent = username;
      } else if (isEmpty && !username) {
        // Si on n'a pas le nom dans les settings, essayer de le récupérer depuis le DOM
        // ou utiliser une valeur par défaut
        const userNameElement = document.querySelector('[data-drupal-user-name]');
        if (userNameElement) {
          userTabLink.textContent = userNameElement.getAttribute('data-drupal-user-name');
        } else {
          // Essayer de trouver le nom dans le header personnalisé
          const headerUserLink = document.querySelector('.header-user-links .account-link');
          if (headerUserLink) {
            const headerText = headerUserLink.textContent.trim();
            if (headerText && headerText !== 'Mon compte') {
              userTabLink.textContent = headerText;
            }
          }
        }
      }
    },

    /**
     * Corrige les liens du menu utilisateur dans la toolbar.
     */
    fixToolbarUserLinks: function (context) {
      const userTray = context.querySelector('.toolbar-tray-user');
      if (!userTray) {
        return;
      }

      const userLinks = userTray.querySelector('.toolbar-menu');
      let needsCreation = false;
      
      if (userLinks) {
        const links = userLinks.querySelectorAll('a');
        // Si le menu est vide ou ne contient que des placeholders
        if (links.length === 0) {
          needsCreation = true;
        } else {
          // Vérifier si tous les liens sont vides ou sont des placeholders
          let allEmpty = true;
          links.forEach(link => {
            const text = link.textContent.trim();
            if (text && text !== '' && text !== '&nbsp;' && text !== '\u00A0' && !link.classList.contains('toolbar-tray-lazy-placeholder-link')) {
              allEmpty = false;
            }
          });
          if (allEmpty) {
            needsCreation = true;
          }
        }
      } else {
        needsCreation = true;
      }

      if (needsCreation) {
        this.createToolbarUserLinks(context, userTray);
      }
    },

    /**
     * Crée les liens utilisateur dans la toolbar.
     */
    createToolbarUserLinks: function (context, userTray) {
      console.log('createToolbarUserLinks called', userTray);
      
      // Récupérer l'ID utilisateur depuis les settings ou le DOM
      let userId = null;
      if (typeof drupalSettings !== 'undefined' && drupalSettings.user && drupalSettings.user.uid) {
        userId = drupalSettings.user.uid;
      } else {
        // Essayer de récupérer depuis l'URL du lien utilisateur
        const userTabLink = document.querySelector('.toolbar-icon-user')?.closest('a');
        if (userTabLink && userTabLink.href) {
          const match = userTabLink.href.match(/\/user\/(\d+)/);
          if (match) {
            userId = match[1];
          }
        }
      }

      // Si on n'a pas l'ID, utiliser 1 par défaut
      if (!userId) {
        userId = '1';
      }
      
      console.log('User ID:', userId);

      // TOUJOURS remplacer le menu existant pour être sûr
      const existingMenu = userTray.querySelector('.toolbar-menu');
      if (existingMenu) {
        // Vérifier si les liens sont vraiment valides
        const existingLinks = existingMenu.querySelectorAll('a');
        let allValid = true;
        if (existingLinks.length < 3) {
          allValid = false;
        } else {
          existingLinks.forEach(link => {
            const text = link.textContent.trim();
            if (!text || text === '' || text === '\u00A0' || text.length < 3 || link.classList.contains('toolbar-tray-lazy-placeholder-link')) {
              allValid = false;
            }
          });
        }
        
        // Si tous les liens sont valides ET qu'ils ont du contenu réel, ne pas recréer
        if (allValid && existingLinks.length >= 3) {
          // Vérifier une dernière fois que les liens fonctionnent
          let hasWorkingLinks = false;
          existingLinks.forEach(link => {
            if (link.href && link.href !== '#' && link.href !== '' && !link.href.includes('placeholder')) {
              hasWorkingLinks = true;
            }
          });
          if (hasWorkingLinks) {
            return; // Les liens sont valides, ne pas recréer
          }
        }
        
        // Sinon, supprimer le menu existant
        existingMenu.remove();
      }

      // Créer la structure des liens dans le format attendu par Drupal
      const menu = document.createElement('ul');
      menu.className = 'toolbar-menu';

      const links = [
        { title: 'Voir le profil', url: '/user/' + userId },
        { title: 'Modifier le profil', url: '/user/' + userId + '/edit' },
        { title: 'Se déconnecter', url: '/user/logout' }
      ];

      links.forEach(link => {
        const li = document.createElement('li');
        const a = document.createElement('a');
        a.href = link.url;
        a.textContent = link.title;
        a.setAttribute('title', link.title);
        // Ajouter les classes Drupal attendues
        a.classList.add('toolbar-icon', 'toolbar-icon-user');
        li.appendChild(a);
        menu.appendChild(li);
      });
      
      // Ajouter le nouveau menu
      userTray.appendChild(menu);
      
      console.log('Menu created and added to tray', menu);
      
      // Forcer le rendu en déclenchant un événement
      const event = new Event('toolbar-user-menu-created', { bubbles: true });
      userTray.dispatchEvent(event);
      
      // Vérifier que les liens sont bien présents
      setTimeout(() => {
        const finalLinks = userTray.querySelectorAll('.toolbar-menu a');
        console.log('Final links count:', finalLinks.length);
        finalLinks.forEach((link, index) => {
          console.log('Link', index, ':', link.textContent, link.href);
        });
      }, 100);
    }
  };

  // Behavior pour intercepter les commandes AJAX et BigPipe
  // Ce code s'exécute après le chargement complet de Drupal
  Drupal.behaviors.spherevoicesToolbarIntercept = {
    attach: function (context, settings) {
      // Ne s'exécuter qu'une seule fois (quand context === document)
      if (context !== document) {
        return;
      }
      
      // Intercepter BigPipe pour remplacer le lazy builder
      if (typeof Drupal !== 'undefined' && Drupal.bigPipe && typeof Drupal.bigPipe.processPlaceholderReplacement === 'function') {
        const originalProcessPlaceholder = Drupal.bigPipe.processPlaceholderReplacement;
        Drupal.bigPipe.processPlaceholderReplacement = function(placeholderReplacement) {
          const result = originalProcessPlaceholder.call(this, placeholderReplacement);
          
          console.log('BigPipe placeholder replacement', placeholderReplacement);
          
          // Si c'est un placeholder pour la toolbar utilisateur, forcer la création des liens
          if (placeholderReplacement && (
            (placeholderReplacement.selector && placeholderReplacement.selector.indexOf('toolbar-tray-user') !== -1) ||
            (placeholderReplacement.command && placeholderReplacement.command === 'insert') ||
            (placeholderReplacement.data && placeholderReplacement.data.indexOf('toolbar-tray-user') !== -1)
          )) {
            setTimeout(function() {
              const userTray = document.querySelector('.toolbar-tray-user');
              if (userTray) {
                console.log('BigPipe: Creating toolbar user links');
                Drupal.behaviors.spherevoicesTheme.createToolbarUserLinks(document, userTray);
              }
            }, 50);
          }
          
          return result;
        };
      }
      
      // Intercepter aussi les commandes AJAX de Drupal
      if (typeof Drupal !== 'undefined' && Drupal.AjaxCommands && Drupal.AjaxCommands.prototype && typeof Drupal.AjaxCommands.prototype.insert === 'function') {
        const originalInsert = Drupal.AjaxCommands.prototype.insert;
        Drupal.AjaxCommands.prototype.insert = function(ajax, response, status) {
          const result = originalInsert.call(this, ajax, response, status);
          
          console.log('AJAX insert command', response);
          
          // Si c'est une insertion dans la toolbar utilisateur, forcer la création des liens
          if (response && (
            (response.selector && response.selector.indexOf('toolbar-tray-user') !== -1) ||
            (response.data && response.data.indexOf('toolbar-tray-user') !== -1) ||
            (response.data && response.data.indexOf('toolbar-menu') !== -1)
          )) {
            setTimeout(function() {
              const userTray = document.querySelector('.toolbar-tray-user');
              if (userTray) {
                console.log('AJAX: Creating toolbar user links');
                Drupal.behaviors.spherevoicesTheme.createToolbarUserLinks(document, userTray);
              }
            }, 50);
          }
          
          return result;
        };
        
        // Intercepter aussi la commande "updateToolbarSubtrees"
        if (Drupal.AjaxCommands && Drupal.AjaxCommands.prototype && Drupal.AjaxCommands.prototype.updateToolbarSubtrees) {
          const originalUpdateSubtrees = Drupal.AjaxCommands.prototype.updateToolbarSubtrees;
          Drupal.AjaxCommands.prototype.updateToolbarSubtrees = function(ajax, response, status) {
            const result = originalUpdateSubtrees.call(this, ajax, response, status);
            
            console.log('AJAX updateToolbarSubtrees command', response);
            
            setTimeout(function() {
              const userTray = document.querySelector('.toolbar-tray-user');
              if (userTray) {
                console.log('updateToolbarSubtrees: Creating toolbar user links');
                Drupal.behaviors.spherevoicesTheme.createToolbarUserLinks(document, userTray);
              }
            }, 100);
            
            return result;
          };
        }
      }
    }
  };

  // Corriger la toolbar utilisateur après le chargement de la page et après les requêtes AJAX
  Drupal.behaviors.spherevoicesToolbarFix = {
    attach: function (context, settings) {
      const self = this;
      
      // Fonction pour corriger la toolbar
      function fixToolbar() {
        Drupal.behaviors.spherevoicesTheme.fixToolbarUser(context);
        Drupal.behaviors.spherevoicesTheme.fixToolbarUserLinks(context);
      }

      // Corriger immédiatement
      fixToolbar();

      // Observer les changements dans la toolbar
      const toolbarObserver = new MutationObserver(() => {
        fixToolbar();
      });

      const toolbar = context.querySelector('#toolbar-bar');
      if (toolbar) {
        toolbarObserver.observe(toolbar, {
          childList: true,
          subtree: true,
          characterData: true
        });
      }

      // Observer spécifiquement l'élément utilisateur
      const userTab = context.querySelector('.toolbar-icon-user');
      if (userTab) {
        const userObserver = new MutationObserver(() => {
          fixToolbar();
        });
        userObserver.observe(userTab.closest('.toolbar-item') || userTab, {
          childList: true,
          subtree: true,
          characterData: true
        });

        // Écouter le clic sur le tab utilisateur pour forcer la création des liens
        const userTabLink = userTab.closest('a');
        if (userTabLink) {
          // Utiliser capture pour intercepter AVANT les autres handlers Drupal
          userTabLink.addEventListener('click', function(e) {
            // Forcer la création TOUJOURS, sans vérification
            const forceCreate = () => {
              const userTray = document.querySelector('.toolbar-tray-user');
              if (userTray) {
                // TOUJOURS créer les liens, même s'ils existent
                Drupal.behaviors.spherevoicesTheme.createToolbarUserLinks(context, userTray);
              }
            };
            
            // Essayer immédiatement (avant que Drupal ne gère le clic)
            setTimeout(forceCreate, 0);
            
            // Puis vérifier plusieurs fois après que le tray soit ouvert
            [10, 50, 100, 200, 300, 500, 800].forEach(delay => {
              setTimeout(forceCreate, delay);
            });
          }, true); // Utiliser capture phase pour intercepter AVANT Drupal
          
          // Aussi intercepter l'événement mousedown pour être encore plus tôt
          userTabLink.addEventListener('mousedown', function(e) {
            setTimeout(() => {
              const userTray = document.querySelector('.toolbar-tray-user');
              if (userTray) {
                Drupal.behaviors.spherevoicesTheme.createToolbarUserLinks(context, userTray);
              }
            }, 0);
          }, true);
        }
        
        // Observer aussi les changements d'état du tray
        const userTray = document.querySelector('.toolbar-tray-user');
        if (userTray) {
          const trayObserver = new MutationObserver((mutations) => {
            // Vérifier si le tray devient visible/actif
            const isActive = userTray.classList.contains('is-active') || 
                           userTray.classList.contains('toolbar-tray-open') ||
                           userTray.style.display !== 'none';
            
            if (isActive) {
              // Forcer la création des liens quand le tray s'ouvre
              setTimeout(() => {
                Drupal.behaviors.spherevoicesTheme.createToolbarUserLinks(context, userTray);
              }, 10);
            }
            
            // Aussi vérifier si les liens sont vides
            const links = userTray.querySelectorAll('.toolbar-menu a:not(.toolbar-tray-lazy-placeholder-link)');
            let hasValidLinks = false;
            links.forEach(link => {
              const text = link.textContent.trim();
              if (text && text !== '' && text !== '&nbsp;' && text !== '\u00A0' && text.length > 2) {
                hasValidLinks = true;
              }
            });
            if (!hasValidLinks || links.length < 3) {
              setTimeout(() => {
                Drupal.behaviors.spherevoicesTheme.createToolbarUserLinks(context, userTray);
              }, 10);
            }
          });
          trayObserver.observe(userTray, {
            attributes: true,
            attributeFilter: ['class', 'style'],
            childList: true,
            subtree: true,
            characterData: true
          });
        }
        
        // Intercepter aussi l'événement d'ouverture du tray de Drupal
        if (typeof Drupal !== 'undefined' && Drupal.toolbar && Drupal.toolbar.models && Drupal.toolbar.models.toolbarModel && typeof Drupal.toolbar.models.toolbarModel.set === 'function') {
          try {
            const toolbarModel = Drupal.toolbar.models.toolbarModel;
            const originalSet = toolbarModel.set.bind(toolbarModel);
            toolbarModel.set = function(key, value) {
              const result = originalSet(key, value);
              if (key === 'activeTab' && value === 'toolbar-item-user') {
                setTimeout(() => {
                  const userTray = document.querySelector('.toolbar-tray-user');
                  if (userTray) {
                    Drupal.behaviors.spherevoicesTheme.createToolbarUserLinks(context, userTray);
                  }
                }, 50);
              }
              return result;
            };
          } catch (e) {
            console.warn('Error intercepting toolbar model:', e);
          }
        }
      }

      // Écouter les événements AJAX de Drupal avec jQuery
      if (typeof jQuery !== 'undefined' && typeof Drupal !== 'undefined' && Drupal.once) {
        const $context = jQuery(context);
        Drupal.once('toolbar-fix', $context, function() {
          $context.on('ajaxComplete', function() {
            setTimeout(fixToolbar, 200);
          });
        });
      } else if (typeof jQuery !== 'undefined') {
        // Fallback si Drupal.once n'est pas disponible
        const $context = jQuery(context);
        if (!$context.data('toolbar-fix-attached')) {
          $context.data('toolbar-fix-attached', true);
          $context.on('ajaxComplete', function() {
            setTimeout(fixToolbar, 200);
          });
        }
      }

      // Écouter les événements de la toolbar Drupal
      if (typeof Drupal !== 'undefined' && Drupal.toolbar && Drupal.toolbar.setSubtrees) {
        const originalResolve = Drupal.toolbar.setSubtrees.resolve;
        if (originalResolve) {
          Drupal.toolbar.setSubtrees.resolve = function(subtrees) {
            originalResolve.call(this, subtrees);
            setTimeout(fixToolbar, 300);
          };
        }
      }

      // Vérifier périodiquement (fallback) - plus fréquent
      setInterval(() => {
        const userTray = document.querySelector('.toolbar-tray-user');
        if (userTray) {
          const isActive = userTray.classList.contains('is-active') || 
                         userTray.classList.contains('toolbar-tray-open');
          if (isActive) {
            const links = userTray.querySelectorAll('.toolbar-menu a:not(.toolbar-tray-lazy-placeholder-link)');
            let hasValidLinks = false;
            links.forEach(link => {
              const text = link.textContent.trim();
              if (text && text !== '' && text !== '&nbsp;' && text !== '\u00A0' && text.length > 2) {
                hasValidLinks = true;
              }
            });
            if (!hasValidLinks || links.length < 3) {
              Drupal.behaviors.spherevoicesTheme.createToolbarUserLinks(context, userTray);
            }
          }
        }
      }, 500);
    }
  };

})(Drupal, jQuery);

