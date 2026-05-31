/**
 * @file
 * Cookie consent banner.
 */

(function (Drupal, drupalSettings, once) {
  'use strict';

  var STORAGE_KEY = 'spherevoices_cookie_consent';
  var DISMISSED_KEY = 'spherevoices_cookie_dismissed';

  function getCookieMode() {
    if (drupalSettings.spherevoicesCookies && drupalSettings.spherevoicesCookies.mode) {
      return drupalSettings.spherevoicesCookies.mode;
    }
    return 'informational';
  }

  function isInformationalMode() {
    return getCookieMode() === 'informational';
  }

  /**
   * @return {string|null}
   *   'accepted', 'rejected', or null if no choice yet.
   */
  function getConsent() {
    try {
      return localStorage.getItem(STORAGE_KEY);
    }
    catch (e) {
      return null;
    }
  }

  function setConsent(value) {
    try {
      localStorage.setItem(STORAGE_KEY, value);
    }
    catch (e) {
      // Ignore private browsing quota errors.
    }
  }

  function isDismissed() {
    try {
      return localStorage.getItem(DISMISSED_KEY) === '1';
    }
    catch (e) {
      return false;
    }
  }

  function setDismissed() {
    try {
      localStorage.setItem(DISMISSED_KEY, '1');
    }
    catch (e) {
      // Ignore.
    }
  }

  /**
   * Loads Google AdSense (shared with adsense.js).
   */
  window.spherevoicesLoadAdsense = window.spherevoicesLoadAdsense || function (clientId) {
    if (!clientId || window.spherevoicesAdsenseLoaded) {
      return;
    }
    var script = document.createElement('script');
    script.async = true;
    script.crossOrigin = 'anonymous';
    script.src = 'https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=' + encodeURIComponent(clientId);
    script.onload = function () {
      document.querySelectorAll('ins.adsbygoogle').forEach(function () {
        try {
          (window.adsbygoogle = window.adsbygoogle || []).push({});
        }
        catch (err) {
          // Script blocked or unit misconfigured.
        }
      });
    };
    document.head.appendChild(script);
    window.spherevoicesAdsenseLoaded = true;
  };

  function maybeLoadAdsense() {
    if (!isInformationalMode() && getConsent() !== 'accepted') {
      return;
    }
    var client = drupalSettings.spherevoicesAds && drupalSettings.spherevoicesAds.client;
    if (client) {
      window.spherevoicesLoadAdsense(client);
    }
  }

  function hideBanner(banner) {
    if (!banner) {
      return;
    }
    banner.classList.remove('cookie-consent--visible');
    banner.setAttribute('hidden', 'hidden');
    document.documentElement.classList.remove('cookie-consent-visible');
  }

  function showBanner(banner) {
    if (!banner) {
      return;
    }
    banner.classList.add('cookie-consent--visible');
    banner.removeAttribute('hidden');
    document.documentElement.classList.add('cookie-consent-visible');
  }

  Drupal.behaviors.spherevoicesCookieConsent = {
    attach: function () {
      var banner = document.getElementById('spherevoices-cookie-banner');
      if (!banner) {
        maybeLoadAdsense();
        return;
      }

      once('spherevoices-cookie-banner', banner).forEach(function (element) {
        // Mode informatif : pubs chargées tout de suite, bandeau jusqu'à fermeture.
        if (isInformationalMode()) {
          maybeLoadAdsense();
          if (isDismissed()) {
            hideBanner(element);
            return;
          }
          showBanner(element);
          var okBtn = element.querySelector('.cookie-consent__btn--accept');
          if (okBtn) {
            okBtn.addEventListener('click', function () {
              setConsent('accepted');
              setDismissed();
              hideBanner(element);
            });
          }
          return;
        }

        // Mode strict : AdSense seulement après acceptation explicite.
        var consent = getConsent();
        if (consent === 'accepted') {
          hideBanner(element);
          maybeLoadAdsense();
          return;
        }
        if (consent === 'rejected') {
          hideBanner(element);
          return;
        }

        showBanner(element);

        var acceptBtn = element.querySelector('.cookie-consent__btn--accept');
        var rejectBtn = element.querySelector('.cookie-consent__btn--reject');

        if (acceptBtn) {
          acceptBtn.addEventListener('click', function () {
            setConsent('accepted');
            hideBanner(element);
            maybeLoadAdsense();
          });
        }
        if (rejectBtn) {
          rejectBtn.addEventListener('click', function () {
            setConsent('rejected');
            hideBanner(element);
          });
        }
      });
    },
  };

})(Drupal, drupalSettings, once);
