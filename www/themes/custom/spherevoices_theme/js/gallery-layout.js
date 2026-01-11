(function () {
  'use strict';

  function adjustGalleryLayout() {
    const sidebar = document.querySelector('.homepage-sidebar');
    const mainColumn = document.querySelector('.homepage-main-column');
    const galleryLayout = document.querySelector('.homepage-gallery-layout');
    const articles = document.querySelectorAll('.homepage-gallery-layout .article-gallery-item');

    if (!sidebar || !galleryLayout || !mainColumn || articles.length === 0) {
      return;
    }

    // Obtenir les positions absolues
    const sidebarRect = sidebar.getBoundingClientRect();
    const sidebarBottom = sidebarRect.top + sidebarRect.height + window.scrollY;
    const galleryTop = galleryLayout.getBoundingClientRect().top + window.scrollY;
    
    const gapSize = 32; // 2rem = 32px
    
    // Calculer ligne par ligne quelle ligne d'articles dépasse la sidebar
    let currentY = galleryTop;
    let articlesNextToSidebar = 0;
    
    // Estimer la hauteur moyenne d'un article
    let avgArticleHeight = 0;
    const sampleSize = Math.min(3, articles.length);
    for (let i = 0; i < sampleSize; i++) {
      avgArticleHeight += articles[i].offsetHeight;
    }
    avgArticleHeight = avgArticleHeight / sampleSize;
    
    // Parcourir les lignes d'articles (3 par ligne)
    while (articlesNextToSidebar < articles.length) {
      const lineBottom = currentY + avgArticleHeight;
      
      // Si le BAS de cette ligne dépasse le bas de la sidebar, on arrête
      if (lineBottom > sidebarBottom + gapSize) {
        break;
      }
      
      articlesNextToSidebar += 3;
      currentY = lineBottom + gapSize;
    }
    
    articlesNextToSidebar = Math.min(articlesNextToSidebar, articles.length);

    // Réinitialiser tous les styles
    articles.forEach(article => {
      article.style.gridRow = '';
      article.style.gridColumn = '';
      article.classList.remove('below-sidebar');
    });

    // Si on a des articles qui dépassent
    if (articlesNextToSidebar < articles.length && articlesNextToSidebar > 0) {
      galleryLayout.classList.add('has-articles-below-sidebar');
      
      // IMPORTANT: Retirer le padding pour que les articles puissent aller sous la sidebar
      mainColumn.style.paddingRight = '0';
      
      // Passer en grille 4 colonnes
      galleryLayout.style.gridTemplateColumns = 'repeat(4, 1fr)';
      
      // Calculer les lignes
      const rowsWith3Cols = Math.ceil(articlesNextToSidebar / 3);
      
      // Positionner les articles
      articles.forEach((article, index) => {
        if (index < articlesNextToSidebar) {
          // Articles à côté de la sidebar : colonnes 1, 2, 3 seulement
          const row = Math.floor(index / 3) + 1;
          const col = (index % 3) + 1;
          article.style.gridRow = row;
          article.style.gridColumn = col;
        } else {
          // Articles sous la sidebar : 4 colonnes
          article.classList.add('below-sidebar');
          const adjustedIndex = index - articlesNextToSidebar;
          const row = Math.floor(adjustedIndex / 4) + rowsWith3Cols + 1;
          const col = (adjustedIndex % 4) + 1;
          article.style.gridRow = row;
          article.style.gridColumn = col;
        }
      });
      
    } else if (articlesNextToSidebar >= articles.length) {
      // Tous les articles à côté de la sidebar
      galleryLayout.classList.remove('has-articles-below-sidebar');
      galleryLayout.style.gridTemplateColumns = 'repeat(3, 1fr)';
      mainColumn.style.paddingRight = 'calc(320px + 2rem)';
    } else {
      // Pas d'articles à côté, tous en 4 colonnes
      galleryLayout.classList.remove('has-articles-below-sidebar');
      galleryLayout.style.gridTemplateColumns = 'repeat(4, 1fr)';
      mainColumn.style.paddingRight = '0';
    }
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
  });
})();
