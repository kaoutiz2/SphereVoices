/**
 * @file
 * Detects ad blockers and shows a neutral fallback message in promo slots.
 */

(function (Drupal, once) {
  'use strict';

  /**
   * Classic bait element: most blockers hide elements with these class names.
   */
  function isBaitBlocked() {
    var bait = document.createElement('div');
    bait.innerHTML = '&nbsp;';
    bait.className = 'adsbox adsbygoogle ad-banner pub_300x250 textAd text-ad text_ads';
    bait.setAttribute('aria-hidden', 'true');
    bait.style.cssText = 'position:absolute!important;left:-9999px!important;top:-9999px!important;width:1px!important;height:1px!important;pointer-events:none!important;';
    document.documentElement.appendChild(bait);
    var style = window.getComputedStyle(bait);
    var blocked = bait.offsetHeight === 0
      || style.display === 'none'
      || style.visibility === 'hidden'
      || style.opacity === '0';
    bait.remove();
    return blocked;
  }

  /**
   * Checks whether real slot content is present but hidden in the DOM.
   */
  function isSlotContentHidden() {
    var probes = document.querySelectorAll('.ad-slot__placeholder-label, .ad-slot__inner--adsense ins.adsbygoogle');
    if (!probes.length) {
      return false;
    }
    for (var i = 0; i < probes.length; i++) {
      var el = probes[i];
      if (el.offsetHeight > 0 && el.offsetWidth > 0) {
        return false;
      }
      var style = window.getComputedStyle(el);
      if (style.display !== 'none' && style.visibility !== 'hidden' && parseFloat(style.opacity) > 0) {
        if (el.offsetHeight > 0 || el.offsetWidth > 0) {
          return false;
        }
      }
    }
    return true;
  }

  function revealFallbacks() {
    document.documentElement.classList.add('sv-promo-blocked');
    once('sv-promo-fallback-show', '[data-sv-promo-fallback]', document).forEach(function (el) {
      el.removeAttribute('hidden');
      el.classList.add('sv-promo-fallback--visible');
    });
    scheduleGalleryReflow();
  }

  function scheduleGalleryReflow() {
    window.setTimeout(function () {
      window.dispatchEvent(new Event('resize'));
    }, 50);
  }

  Drupal.behaviors.spherevoicesAdBlockFallback = {
    attach: function (context) {
      once('sv-promo-blocked-detect', 'html', context).forEach(function () {
        window.setTimeout(function () {
          if (isBaitBlocked() || isSlotContentHidden()) {
            revealFallbacks();
          }
          else {
            scheduleGalleryReflow();
          }
        }, 120);
      });
    },
  };

})(Drupal, once);
