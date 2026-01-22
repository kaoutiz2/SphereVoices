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
      
      $('.poll-choice-item', $wrapper).each(function () {
        var $textField = $('input[type="text"]', this);
        var $votesField = $('input[type="hidden"]', this);
        var text = $textField.val();
        
        if (text && text.trim() !== '') {
          choices.push({
            text: text.trim(),
            votes: parseInt($votesField.val()) || 0
          });
        }
      });
      
      // Mettre à jour le champ caché
      var $hiddenField = $wrapper.find('input[type="hidden"][name*="[value]"]');
      if ($hiddenField.length) {
        $hiddenField.val(JSON.stringify(choices));
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
