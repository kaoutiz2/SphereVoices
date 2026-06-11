/**
 * @file
 * Lightbox pour la galerie photo de la page d'accueil.
 * Aucune dépendance externe — pur JS natif.
 */
(function (Drupal, once) {
  'use strict';

  Drupal.behaviors.photoGallery = {
    attach: function (context) {
      once('photo-gallery', '[data-gallery]', context).forEach(function (grid) {
        var lightbox    = document.querySelector('.js-gallery-lightbox');
        var imgEl       = document.querySelector('.js-gallery-img');
        var counterEl   = document.querySelector('.js-gallery-counter');
        var closeBtn    = document.querySelector('.js-gallery-close');
        var prevBtn     = document.querySelector('.js-gallery-prev');
        var nextBtn     = document.querySelector('.js-gallery-next');

        if (!lightbox || !imgEl) return;

        // Collecter toutes les photos de la galerie.
        var items = Array.from(grid.querySelectorAll('.js-gallery-open'));
        var total  = items.length;
        var current = 0;

        function show(index) {
          if (total === 0) return;
          current = (index + total) % total;
          var item = items[current];
          var full = item.getAttribute('data-full');
          var alt  = item.getAttribute('data-alt') || '';

          imgEl.classList.add('is-loading');
          var tmp = new Image();
          tmp.onload = function () {
            imgEl.src = full;
            imgEl.alt = alt;
            imgEl.classList.remove('is-loading');
          };
          tmp.onerror = function () {
            imgEl.src = full;
            imgEl.alt = alt;
            imgEl.classList.remove('is-loading');
          };
          tmp.src = full;

          if (counterEl) {
            counterEl.textContent = (current + 1) + ' / ' + total;
          }
          prevBtn && (prevBtn.style.display = total > 1 ? '' : 'none');
          nextBtn && (nextBtn.style.display = total > 1 ? '' : 'none');
        }

        function open(index) {
          show(index);
          lightbox.removeAttribute('hidden');
          document.body.style.overflow = 'hidden';
          closeBtn && closeBtn.focus();
        }

        function close() {
          lightbox.setAttribute('hidden', '');
          document.body.style.overflow = '';
          if (items[current]) items[current].focus();
        }

        // Ouvrir au clic sur une vignette.
        items.forEach(function (btn, i) {
          btn.addEventListener('click', function () { open(i); });
        });

        // Fermer.
        closeBtn && closeBtn.addEventListener('click', close);
        lightbox.addEventListener('click', function (e) {
          if (e.target === lightbox) close();
        });

        // Navigation.
        prevBtn && prevBtn.addEventListener('click', function () { show(current - 1); });
        nextBtn && nextBtn.addEventListener('click', function () { show(current + 1); });

        // Clavier.
        document.addEventListener('keydown', function (e) {
          if (lightbox.hasAttribute('hidden')) return;
          if (e.key === 'Escape')      close();
          if (e.key === 'ArrowLeft')   show(current - 1);
          if (e.key === 'ArrowRight')  show(current + 1);
        });
      });
    }
  };

})(Drupal, once);
