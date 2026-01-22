(function () {
  'use strict';

  console.log('[fix-image-preview] Script loaded');

  /**
   * Remplace les URLs des images thumbnail par les URLs originales
   * dans les previews du widget d'image.
   */
  function replaceImageStyleUrls() {
    console.log('[fix-image-preview] Running replaceImageStyleUrls');
    
    // Trouver toutes les images avec la classe image-style-thumbnail
    const thumbnailImages = document.querySelectorAll('img.image-style-thumbnail, img[src*="/styles/thumbnail/"]');
    
    console.log('[fix-image-preview] Found', thumbnailImages.length, 'thumbnail images');
    
    thumbnailImages.forEach(function(img, index) {
      const src = img.getAttribute('src');
      console.log('[fix-image-preview] Processing image', index, ':', src);
      
      // Vérifier si l'URL contient /styles/thumbnail/
      if (src && src.includes('/styles/thumbnail/')) {
        // Format attendu: /sites/default/files/styles/thumbnail/public/YYYY-MM/filename.ext.webp?itok=...
        // On veut extraire: YYYY-MM/filename.ext (sans le .webp et sans ?itok)
        
        // Pattern: .../styles/thumbnail/public/[CHEMIN](.webp)?(?itok=...)?
        const regex = /\/styles\/thumbnail\/public\/(.+?)(\?|$)/;
        const match = src.match(regex);
        
        if (match) {
          let relativePath = match[1]; // Ex: "2025-11/image.jpg.webp" ou "2025-11/image.jpg"
          
          console.log('[fix-image-preview] Extracted path:', relativePath);
          
          // Si le chemin se termine par .webp, on le retire
          if (relativePath.endsWith('.webp')) {
            relativePath = relativePath.slice(0, -5); // Retire ".webp"
            console.log('[fix-image-preview] Removed .webp:', relativePath);
          }
          
          // Reconstruire l'URL originale
          const originalUrl = '/sites/default/files/' + relativePath;
          
          console.log('[fix-image-preview] Replacing:', src, '->', originalUrl);
          
          // Remplacer l'URL
          img.setAttribute('src', originalUrl);
          
          // Retirer les attributs width et height
          img.removeAttribute('width');
          img.removeAttribute('height');
        } else {
          console.log('[fix-image-preview] No match for regex');
        }
      }
    });
  }

  // Exécuter immédiatement
  replaceImageStyleUrls();

  // Exécuter au chargement de la page
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function() {
      console.log('[fix-image-preview] DOMContentLoaded event');
      setTimeout(replaceImageStyleUrls, 100);
    });
  }

  // Re-exécuter après chargement complet
  window.addEventListener('load', function() {
    console.log('[fix-image-preview] Load event');
    setTimeout(replaceImageStyleUrls, 100);
  });

  // Observer les changements DOM pour capturer les previews ajoutées dynamiquement
  const observer = new MutationObserver(function(mutations) {
    let hasImageChanges = false;
    
    mutations.forEach(function(mutation) {
      if (mutation.addedNodes.length) {
        mutation.addedNodes.forEach(function(node) {
          if (node.nodeType === 1) { // Element node
            if (node.tagName === 'IMG' || node.querySelector('img')) {
              hasImageChanges = true;
            }
          }
        });
      }
    });
    
    if (hasImageChanges) {
      console.log('[fix-image-preview] DOM mutation detected');
      setTimeout(replaceImageStyleUrls, 100);
    }
  });

  // Observer le body pour les changements
  if (document.body) {
    observer.observe(document.body, {
      childList: true,
      subtree: true
    });
  } else {
    document.addEventListener('DOMContentLoaded', function() {
      observer.observe(document.body, {
        childList: true,
        subtree: true
      });
    });
  }
  
  console.log('[fix-image-preview] Observer started');
})();

