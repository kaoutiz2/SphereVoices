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

