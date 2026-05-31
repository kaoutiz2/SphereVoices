/**
 * @file
 * Initializes Google AdSense units.
 */

(function (Drupal, drupalSettings, once) {
  'use strict';

  function canLoadAdsense() {
    if (drupalSettings.spherevoicesCookies &&
      drupalSettings.spherevoicesCookies.mode === 'informational') {
      return true;
    }
    try {
      return localStorage.getItem('spherevoices_cookie_consent') === 'accepted';
    }
    catch (e) {
      return false;
    }
  }

  Drupal.behaviors.spherevoicesAdsense = {
    attach: function (context) {
      if (!canLoadAdsense()) {
        return;
      }

      var client = drupalSettings.spherevoicesAds && drupalSettings.spherevoicesAds.client;
      if (client && typeof window.spherevoicesLoadAdsense === 'function') {
        window.spherevoicesLoadAdsense(client);
        return;
      }

      once('spherevoices-adsense', 'ins.adsbygoogle', context).forEach(function () {
        try {
          (window.adsbygoogle = window.adsbygoogle || []).push({});
        }
        catch (e) {
          // AdSense may throw if the script is blocked or not yet loaded.
        }
      });
    },
  };

})(Drupal, drupalSettings, once);
