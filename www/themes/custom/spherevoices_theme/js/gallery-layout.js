(function () {
  'use strict';

  var resizeTimer;
  var DESKTOP_MIN = 1200;
  var GAP_PX = 32;

  function getGalleryCells(layout) {
    return Array.prototype.filter.call(layout.children, function (el) {
      return el.nodeType === 1 && (
        el.classList.contains('article-gallery-item') ||
        el.classList.contains('ad-gallery-slot')
      );
    });
  }

  function clearPlacement(el) {
    el.style.gridColumn = '';
    el.style.gridRow = '';
    el.style.alignSelf = '';
  }

  function adjustGalleryLayout() {
    var layout = document.querySelector('.homepage-gallery-layout');
    if (!layout) {
      return;
    }

    var breaking = layout.querySelector('.breaking-news-in-grid');
    var sidebar = layout.querySelector('.homepage-sidebar');
    var articles = getGalleryCells(layout);

    if (breaking) {
      breaking.style.gridColumn = '1 / 4';
      breaking.style.gridRow = '1';
    }

    articles.forEach(clearPlacement);

    if (sidebar) {
      clearPlacement(sidebar);
      sidebar.style.gridColumn = '4';
      sidebar.style.alignSelf = 'start';
    }

    if (!sidebar || window.innerWidth < DESKTOP_MIN) {
      if (sidebar) {
        sidebar.style.gridRow = '1';
      }
      articles.forEach(function (article, index) {
        article.style.gridColumn = String((index % 4) + 1);
        article.style.gridRow = '';
      });
      return;
    }

    var gapSize = GAP_PX;
    var avgHeight = 420;
    if (articles.length > 0) {
      var sampleSize = Math.min(3, articles.length);
      var sum = 0;
      for (var i = 0; i < sampleSize; i++) {
        sum += articles[i].offsetHeight;
      }
      avgHeight = sum / sampleSize;
    }
    var rowHeight = avgHeight + gapSize;
    var sidebarRows = Math.max(1, Math.ceil(sidebar.offsetHeight / rowHeight));
    sidebar.style.gridRow = '1 / span ' + sidebarRows;

    var hasBreaking = !!breaking;
    var currentRow = hasBreaking ? 2 : 1;
    var sidebarEndRow = 1 + sidebarRows;
    var articlesInRow = 0;
    var underSidebar = true;

    articles.forEach(function (article) {
      var stillUnderSidebar = currentRow < sidebarEndRow;

      if (stillUnderSidebar && underSidebar) {
        article.style.gridColumn = String(articlesInRow + 1);
        article.style.gridRow = String(currentRow);
        articlesInRow++;
        if (articlesInRow >= 3) {
          currentRow++;
          articlesInRow = 0;
        }
      }
      else {
        if (underSidebar) {
          if (articlesInRow > 0) {
            currentRow++;
            articlesInRow = 0;
          }
          underSidebar = false;
        }
        article.style.gridColumn = String(articlesInRow + 1);
        article.style.gridRow = String(currentRow);
        articlesInRow++;
        if (articlesInRow >= 4) {
          currentRow++;
          articlesInRow = 0;
        }
      }
    });
  }

  function scheduleAdjust() {
    clearTimeout(resizeTimer);
    resizeTimer = setTimeout(adjustGalleryLayout, 200);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function () {
      setTimeout(adjustGalleryLayout, 150);
    });
  }
  else {
    setTimeout(adjustGalleryLayout, 150);
  }

  window.addEventListener('resize', scheduleAdjust);
  window.addEventListener('load', function () {
    setTimeout(adjustGalleryLayout, 150);
    if (typeof ResizeObserver === 'undefined') {
      return;
    }
    var layout = document.querySelector('.homepage-gallery-layout');
    if (!layout) {
      return;
    }
    var ro = new ResizeObserver(scheduleAdjust);
    ro.observe(layout);
    var sidebar = layout.querySelector('.homepage-sidebar');
    if (sidebar) {
      ro.observe(sidebar);
    }
  });
})();
