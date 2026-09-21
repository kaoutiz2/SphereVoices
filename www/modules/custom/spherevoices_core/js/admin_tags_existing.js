/**
 * @file
 * Étiquettes existantes (formulaire article) : met en avant, dans la liste de
 * consultation, les étiquettes qui correspondent à ce que l'éditeur saisit,
 * pour l'aider à éviter de créer un doublon.
 */

(function (Drupal, once) {
  'use strict';

  // Minuscules + sans accents, pour comparer « Élection » et « election ».
  function normalize(value) {
    return value.normalize('NFD').replace(/[̀-ͯ]/g, '').toLowerCase().trim();
  }

  Drupal.behaviors.svExistingTags = {
    attach: function (context) {
      once('sv-existing-tags', '[data-sv-existing-tags]', context).forEach(function (details) {
        var wrapper = details.closest('.field--name-field-tags') || details.parentElement;
        var input = wrapper.querySelector('input.form-autocomplete, input[data-drupal-selector*="field-tags"]');
        if (!input) {
          return;
        }
        var chips = Array.prototype.slice.call(details.querySelectorAll('.sv-existing-tags__chip'));
        var names = chips.map(function (chip) {
          return normalize(chip.textContent);
        });

        function update() {
          // Les étiquettes sont séparées par des virgules : on ne regarde que la dernière saisie.
          var fragment = normalize(input.value.split(',').pop());
          var matches = 0;
          chips.forEach(function (chip, i) {
            var isMatch = fragment !== '' && names[i].indexOf(fragment) !== -1;
            chip.classList.toggle('is-match', isMatch);
            if (isMatch) {
              matches++;
            }
          });
          details.classList.toggle('sv-existing-tags--filtering', fragment !== '');
          // Ouvre la liste dès qu'une correspondance existe (jamais de fermeture forcée).
          if (fragment.length >= 2 && matches > 0) {
            details.open = true;
          }
        }

        input.addEventListener('input', update);
        update();
      });
    }
  };

})(Drupal, once);
