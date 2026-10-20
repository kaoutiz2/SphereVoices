/**
 * @file
 * Embeds de posts sociaux dans le corps des articles (X, Instagram, TikTok, Facebook).
 *
 * Le HTML (blockquote / div officiels de chaque réseau) est produit côté serveur
 * par SocialEmbedConverter ; ce script charge, une seule fois par réseau et
 * seulement quand l'embed approche de l'écran, le script officiel du réseau.
 *
 * - Respecte le consentement cookies : en mode « strict », rien n'est chargé
 *   avant l'acceptation (le lien de repli reste cliquable).
 * - Si un script est bloqué (bloqueur de pub, réseau), le lien de repli reste.
 */

(function (Drupal, drupalSettings, once) {
  'use strict';

  var CONSENT_KEY = 'spherevoices_cookie_consent';

  var SCRIPTS = {
    twitter: 'https://platform.twitter.com/widgets.js',
    instagram: 'https://www.instagram.com/embed.js',
    tiktok: 'https://www.tiktok.com/embed.js',
    facebook: 'https://connect.facebook.net/fr_FR/sdk.js#xfbml=1&version=v21.0'
  };

  var loading = {};
  var pending = [];
  var consentListenerBound = false;

  /**
   * Le chargement de scripts tiers est-il autorisé ?
   *
   * Mode « informational » (ou réglage absent, ex. aperçu en thème admin) :
   * oui. Mode strict : seulement après acceptation explicite.
   */
  function consentAllows() {
    var cookies = drupalSettings.spherevoicesCookies;
    if (!cookies || !cookies.mode || cookies.mode === 'informational') {
      return true;
    }
    try {
      return window.localStorage.getItem(CONSENT_KEY) === 'accepted';
    }
    catch (e) {
      return false;
    }
  }

  /**
   * Le script du réseau est-il déjà chargé et prêt à traiter des embeds ?
   */
  function isReady(provider) {
    switch (provider) {
      case 'twitter':
        return !!(window.twttr && window.twttr.widgets);

      case 'instagram':
        return !!(window.instgrm && window.instgrm.Embeds);

      case 'facebook':
        return !!(window.FB && window.FB.XFBML);

      default:
        return false;
    }
  }

  /**
   * Charge le script d'un réseau (une seule fois, réutilise un script déjà présent).
   */
  function loadScript(provider) {
    if (loading[provider]) {
      return loading[provider];
    }
    loading[provider] = new Promise(function (resolve, reject) {
      if (isReady(provider)) {
        resolve();
        return;
      }
      var src = SCRIPTS[provider];
      var base = src.split('#')[0];
      var existing = document.querySelector('script[src^="' + base + '"]');
      if (existing) {
        if (existing.getAttribute('data-sv-loaded') === '1') {
          resolve();
          return;
        }
        existing.addEventListener('load', function () {
          existing.setAttribute('data-sv-loaded', '1');
          resolve();
        });
        existing.addEventListener('error', reject);
        // Script déjà chargé par ailleurs (ex. bibliothèque du champ « Tweet »).
        if (existing.readyState === 'complete' || existing.readyState === 'loaded') {
          resolve();
        }
        return;
      }
      var script = document.createElement('script');
      script.async = true;
      script.charset = 'utf-8';
      // Comme dans le code officiel de chaque réseau : seul le SDK Facebook
      // demande crossorigin (TikTok n'envoie pas d'en-têtes CORS : le script
      // serait bloqué).
      if (provider === 'facebook') {
        script.crossOrigin = 'anonymous';
      }
      script.src = src;
      script.onload = function () {
        script.setAttribute('data-sv-loaded', '1');
        resolve();
      };
      script.onerror = reject;
      document.head.appendChild(script);
    });
    // Un échec (script bloqué) ne doit pas empêcher un nouvel essai ultérieur.
    loading[provider].catch(function () {
      delete loading[provider];
    });
    return loading[provider];
  }

  /**
   * Demande au script du réseau de (re)traiter les embeds du conteneur.
   *
   * Les scripts traitent déjà tout le DOM présent à leur chargement ; ces appels
   * couvrent le cas où le script était chargé avant l'ajout de l'embed.
   */
  function process(provider, container) {
    try {
      if (provider === 'twitter' && window.twttr && window.twttr.widgets) {
        window.twttr.widgets.load(container);
      }
      else if (provider === 'instagram' && window.instgrm && window.instgrm.Embeds) {
        window.instgrm.Embeds.process();
      }
      else if (provider === 'facebook' && window.FB && window.FB.XFBML) {
        window.FB.XFBML.parse(container);
      }
    }
    catch (e) {
      // Le lien de repli reste affiché.
    }
  }

  /**
   * Facebook : conteneur racine requis + largeur adaptée à l'écran.
   */
  function prepareFacebook(container) {
    if (!document.getElementById('fb-root')) {
      var root = document.createElement('div');
      root.id = 'fb-root';
      document.body.appendChild(root);
    }
    var available = container.clientWidth || container.parentElement.clientWidth || 500;
    container.querySelectorAll('.fb-post, .fb-video').forEach(function (el) {
      // Largeur acceptée par Facebook : 350 à 750 px.
      el.setAttribute('data-width', String(Math.max(350, Math.min(500, Math.floor(available)))));
    });
  }

  function activate(container) {
    var provider = container.getAttribute('data-sv-social');
    if (!SCRIPTS[provider]) {
      return;
    }
    if (provider === 'facebook') {
      prepareFacebook(container);
    }
    loadScript(provider).then(function () {
      process(provider, container);
    }).catch(function () {
      // Script bloqué : le lien de repli reste visible.
    });
  }

  function activateAll(containers) {
    if (!('IntersectionObserver' in window)) {
      containers.forEach(activate);
      return;
    }
    // Chargement différé : on ne charge le script d'un réseau que lorsque son
    // premier embed approche de l'écran (les scripts tiers sont lourds).
    var observer = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          observer.unobserve(entry.target);
          activate(entry.target);
        }
      });
    }, {rootMargin: '600px 0px'});
    containers.forEach(function (container) {
      observer.observe(container);
    });
  }

  /**
   * Mode strict : active les embeds en attente dès que l'utilisateur accepte.
   */
  function bindConsentListener() {
    if (consentListenerBound) {
      return;
    }
    consentListenerBound = true;
    document.addEventListener('click', function (event) {
      if (!event.target.closest || !event.target.closest('.cookie-consent__btn--accept')) {
        return;
      }
      // Laisse cookie-consent.js enregistrer le choix avant de le relire.
      window.setTimeout(function () {
        if (pending.length && consentAllows()) {
          var toActivate = pending;
          pending = [];
          activateAll(toActivate);
        }
      }, 50);
    });
  }

  Drupal.behaviors.spherevoicesSocialEmbeds = {
    attach: function (context) {
      var containers = once('sv-social-embed', '[data-sv-social]', context);
      if (!containers.length) {
        return;
      }
      if (!consentAllows()) {
        pending = pending.concat(containers);
        bindConsentListener();
        return;
      }
      activateAll(containers);
    }
  };

})(Drupal, drupalSettings, once);
