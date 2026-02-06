/**
 * @file
 * JavaScript pour le widget de choix de sondage.
 */

(function ($, Drupal) {
  'use strict';

  /**
   * Met à jour le champ caché avec les valeurs JSON.
   */
  function updateHiddenField(context) {
    $('.poll-choice-item', context).each(function () {
      var $item = $(this);
      var $wrapper = $item.closest('.field--widget-poll-choices-widget');
      var choices = [];
      var totalVotes = 0;
      
      $('.poll-choice-item', $wrapper).each(function () {
        var $textField = $('input[type="text"]', this);
        var $votesField = $('input[name*="[votes]"]', this);
        var text = $textField.val();
        var votes = parseInt($votesField.val(), 10) || 0;
        
        if (text && text.trim() !== '') {
          choices.push({
            text: text.trim(),
            votes: votes
          });
          totalVotes += votes;
        }
      });
      
      // Mettre à jour le champ caché
      var $hiddenField = $wrapper.find('input[type="hidden"][name*="[value]"]');
      if ($hiddenField.length) {
        $hiddenField.val(JSON.stringify(choices));
      }

      // Mettre à jour l'affichage des pourcentages dans le widget.
      if (totalVotes > 0) {
        $('.poll-choice-item', $wrapper).each(function (index) {
          var $textField = $('input[type="text"]', this);
          var $votesField = $('input[name*="[votes]"]', this);
          var text = $textField.val();
          var votes = parseInt($votesField.val(), 10) || 0;

          if (text && text.trim() !== '') {
            var percentage = (votes / totalVotes) * 100;
            percentage = Math.round(percentage * 10) / 10; // 1 décimale.
            $(this).find('.poll-choice-percentage-display').text(percentage + '%');
          }
          else {
            $(this).find('.poll-choice-percentage-display').text('0%');
          }
        });
      }
      else {
        // Si total 0, tout le monde à 0%.
        $('.poll-choice-item', $wrapper).find('.poll-choice-percentage-display').text('0%');
      }
    });
  }

  /**
   * Comportement pour le widget de choix de sondage.
   */
  Drupal.behaviors.pollChoicesWidget = {
    attach: function (context, settings) {
      // Mettre à jour le champ caché quand un champ texte change
      $('.poll-choice-item input[type="text"]', context).on('blur', function () {
        updateHiddenField(context);
      });
      
      // Mettre à jour après les actions AJAX
      $(context).on('ajaxComplete', function () {
        setTimeout(function () {
          updateHiddenField(context);
        }, 100);
      });
    }
  };

})(jQuery, Drupal);
