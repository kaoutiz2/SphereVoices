(function () {
  'use strict';

  function adjustBrevesHeight() {
    const breakingNews = document.querySelector('.breaking-news-section.breaking-news-in-grid');
    const brevesWidget = document.querySelector('.sidebar-breves-widget');
    const brevesBlock = document.querySelector('.sidebar-breves-widget .breves-block');

    if (!breakingNews || !brevesWidget || !brevesBlock) {
      return;
    }

    // Obtenir la hauteur réelle du breaking news
    const breakingNewsHeight = breakingNews.offsetHeight;

    // Appliquer la même hauteur au widget de brèves
    if (breakingNewsHeight > 0) {
      brevesWidget.style.height = breakingNewsHeight + 'px';
      brevesWidget.style.minHeight = breakingNewsHeight + 'px';
      brevesBlock.style.height = '100%';
      brevesBlock.style.minHeight = '100%';
    }
  }

  // Exécuter au chargement
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function() {
      setTimeout(adjustBrevesHeight, 150);
    });
  } else {
    setTimeout(adjustBrevesHeight, 150);
  }

  // Re-calculer après chargement des images
  window.addEventListener('load', function() {
    setTimeout(adjustBrevesHeight, 150);
  });

  // Re-calculer lors du redimensionnement
  let resizeTimer;
  window.addEventListener('resize', function() {
    clearTimeout(resizeTimer);
    resizeTimer = setTimeout(adjustBrevesHeight, 250);
  });

  // Observer les changements de hauteur du breaking news
  const breakingNews = document.querySelector('.breaking-news-section.breaking-news-in-grid');
  if (breakingNews && typeof ResizeObserver !== 'undefined') {
    const resizeObserver = new ResizeObserver(function(entries) {
      clearTimeout(resizeTimer);
      resizeTimer = setTimeout(adjustBrevesHeight, 250);
    });
    resizeObserver.observe(breakingNews);
  }
})();
