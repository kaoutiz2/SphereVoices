(function () {
  'use strict';

  function adjustGalleryLayout() {
    const sidebar = document.querySelector('.homepage-sidebar');
    const galleryLayout = document.querySelector('.homepage-gallery-layout');
    const articles = document.querySelectorAll('.homepage-gallery-layout .article-gallery-item');

    if (!galleryLayout) {
      return;
    }

    // Si pas de sidebar, tous les articles utilisent les 4 colonnes
    if (!sidebar) {
      articles.forEach((article, index) => {
        const columnNumber = (index % 4) + 1;
        article.style.gridColumn = columnNumber.toString();
        article.style.gridRow = '';
      });
      return;
    }

    // Vérifier si le breaking news est présent
    const breakingNews = document.querySelector('.breaking-news-in-grid');
    const hasBreakingNews = breakingNews !== null;
    
    // Positionner la sidebar dans la 4ème colonne, ligne 1 (même ligne que breaking news)
    sidebar.style.gridColumn = '4';
    sidebar.style.gridRow = '1';
    sidebar.style.alignSelf = 'start';
    
    // S'assurer que le breaking news prend bien 3 colonnes
    if (breakingNews) {
      breakingNews.style.gridColumn = '1 / span 3';
      breakingNews.style.gridRow = '1';
    }

    // Obtenir la position de la sidebar pour savoir où elle se termine
    const sidebarRect = sidebar.getBoundingClientRect();
    const sidebarBottom = sidebarRect.bottom + window.scrollY;
    const sidebarTop = sidebarRect.top + window.scrollY;

    // Calculer la hauteur moyenne des articles
    const gapSize = 32; // 2rem
    let avgArticleHeight = 0;
    if (articles.length > 0) {
      const sampleSize = Math.min(3, articles.length);
      for (let i = 0; i < sampleSize; i++) {
        avgArticleHeight += articles[i].offsetHeight;
      }
      avgArticleHeight = avgArticleHeight / sampleSize;
    }
    const articleHeightWithGap = avgArticleHeight + gapSize;

    // Calculer combien de lignes la sidebar occupe réellement
    const sidebarHeight = sidebarRect.height;
    const sidebarRows = Math.ceil(sidebarHeight / articleHeightWithGap);
    
    // Si le breaking news est présent, on doit tenir compte de sa hauteur
    let breakingNewsHeight = 0;
    if (hasBreakingNews && breakingNews) {
      breakingNewsHeight = breakingNews.offsetHeight + gapSize;
    }
    
    // Calculer à partir de quelle ligne la sidebar se termine
    // La sidebar commence à la ligne 1 et occupe sidebarRows lignes
    const sidebarEndRow = 1 + sidebarRows;
    
    // Les articles commencent à la ligne 2 si le breaking news est présent, sinon ligne 1
    let currentRow = hasBreakingNews ? 2 : 1;
    let articlesInCurrentRow = 0;
    let isUnderSidebar = true;

    articles.forEach((article, index) => {
      // Réinitialiser les styles
      article.style.gridColumn = '';
      article.style.gridRow = '';
      article.style.alignSelf = 'start';

      // Vérifier si cet article est encore sous la sidebar en utilisant la ligne de la grille
      // Si la ligne actuelle est inférieure à la ligne de fin de la sidebar, l'article est sous la sidebar
      const articleStillUnderSidebar = currentRow < sidebarEndRow;

      if (articleStillUnderSidebar && isUnderSidebar) {
        // L'article est encore sous la sidebar : utiliser les colonnes 1-3
        const columnNumber = articlesInCurrentRow + 1; // 1, 2, ou 3
        article.style.gridColumn = columnNumber.toString();
        article.style.gridRow = currentRow;
        articlesInCurrentRow++;
        
        // Si on a 3 articles dans cette ligne, passer à la ligne suivante
        if (articlesInCurrentRow >= 3) {
          currentRow++;
          articlesInCurrentRow = 0;
        }
      } else {
        // L'article n'est plus sous la sidebar : peut utiliser les 4 colonnes
        if (isUnderSidebar) {
          // Finir la ligne actuelle si nécessaire
          if (articlesInCurrentRow > 0) {
            currentRow++;
            articlesInCurrentRow = 0;
          }
          isUnderSidebar = false;
        }
        
        // Utiliser les 4 colonnes (1-4)
        const columnNumber = articlesInCurrentRow + 1; // 1, 2, 3, ou 4
        article.style.gridColumn = columnNumber.toString();
        article.style.gridRow = currentRow;
        articlesInCurrentRow++;
        
        // Si on a 4 articles dans cette ligne, passer à la ligne suivante
        if (articlesInCurrentRow >= 4) {
          currentRow++;
          articlesInCurrentRow = 0;
        }
      }
    });

    // Positionner la sidebar pour qu'elle occupe les lignes nécessaires
    // La sidebar commence toujours à la ligne 1
    sidebar.style.gridRow = '1 / span ' + Math.max(1, sidebarRows);
  }

  // Exécuter au chargement
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function() {
      setTimeout(adjustGalleryLayout, 150);
    });
  } else {
    setTimeout(adjustGalleryLayout, 150);
  }

  // Re-calculer lors du redimensionnement
  let resizeTimer;
  window.addEventListener('resize', function() {
    clearTimeout(resizeTimer);
    resizeTimer = setTimeout(adjustGalleryLayout, 250);
  });

  // Re-calculer après chargement des images
  window.addEventListener('load', function() {
    setTimeout(adjustGalleryLayout, 150);
    
    // Observer les changements de hauteur de la sidebar (pour le sondage qui peut grandir)
    const sidebar = document.querySelector('.homepage-sidebar');
    if (sidebar && typeof ResizeObserver !== 'undefined') {
      const resizeObserver = new ResizeObserver(function(entries) {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(adjustGalleryLayout, 250);
      });
      resizeObserver.observe(sidebar);
    }
  });
})();
