/**
 * @file
 * JavaScript pour le bloc de sondage.
 */

(function ($, Drupal) {
  'use strict';
  
  console.log('Poll block JavaScript loaded');

  /**
   * Met à jour l'affichage des résultats du sondage.
   */
  function updatePollResults($block, results, selectedIndex) {
    console.log('Updating poll results:', results, 'selectedIndex:', selectedIndex);
    results.forEach(function (result) {
      var $choice = $block.find('.poll-choice[data-choice-index="' + result.index + '"]');
      if ($choice.length) {
        console.log('Updating choice', result.index, 'with', result.votes, 'votes');
        
        // Mettre à jour les votes - chercher dans poll-choice-info
        var $votesContainer = $choice.find('.poll-choice-info .poll-votes');
        if (!$votesContainer.length) {
          // Essayer sans poll-choice-info
          $votesContainer = $choice.find('.poll-votes');
        }
        if ($votesContainer.length) {
          var votesText = result.votes + ' vote' + (result.votes > 1 ? 's' : '');
          $votesContainer.html('<span class="poll-votes-count">' + result.votes + '</span> vote' + (result.votes > 1 ? 's' : ''));
          console.log('Updated votes container:', $votesContainer[0], 'to', votesText);
        } else {
          console.warn('Votes container not found for choice', result.index);
        }
        
        // Mettre à jour le pourcentage
        var $percentageContainer = $choice.find('.poll-percentage');
        if ($percentageContainer.length) {
          $percentageContainer.html(result.percentage + '%');
          console.log('Updated percentage to', result.percentage + '%');
        } else {
          console.warn('Percentage container not found for choice', result.index);
        }
        
        // Mettre à jour la barre avec animation
        var $bar = $choice.find('.poll-bar');
        if ($bar.length) {
          $bar.css({
            'width': result.percentage + '%',
            'transition': 'width 0.5s ease'
          });
          console.log('Updated bar width to', result.percentage + '%');
        } else {
          console.warn('Bar not found for choice', result.index);
        }
        
        // Marquer le choix sélectionné
        if (result.index === selectedIndex) {
          $choice.addClass('poll-choice-selected');
          $choice.find('.poll-vote-button, .poll-choice-button').addClass('poll-choice-selected-button');
        }
      } else {
        console.warn('Choice not found for index', result.index);
      }
    });
    
    // Désactiver tous les boutons après le vote
    $('.poll-vote-button, .poll-choice-button', $block).prop('disabled', true);
    
    // Mettre à jour le total des votes
    var totalVotes = results.reduce(function (sum, result) {
      return sum + result.votes;
    }, 0);
    $block.attr('data-total-votes', totalVotes);
    $block.attr('data-voted', 'true');
    
    // Stocker le vote dans localStorage
    var pollId = $block.attr('data-poll-id');
    if (pollId) {
      localStorage.setItem('poll_voted_' + pollId, selectedIndex);
    }
  }
  
  /**
   * Vérifie si l'utilisateur a déjà voté pour ce sondage.
   */
  function hasVoted(pollId) {
    return localStorage.getItem('poll_voted_' + pollId) !== null;
  }
  
  /**
   * Récupère le choix voté pour ce sondage.
   */
  function getVotedChoice(pollId) {
    return parseInt(localStorage.getItem('poll_voted_' + pollId), 10);
  }

  /**
   * Comportement pour le bloc de sondage.
   */
  Drupal.behaviors.pollBlock = {
    attach: function (context, settings) {
      console.log('Poll block behavior attach called, context:', context);
      
      // Initialiser les blocs de sondage une seule fois (utiliser une classe pour marquer)
      $('.poll-block:not(.poll-block-initialized)', context).each(function () {
        var $block = $(this);
        $block.addClass('poll-block-initialized');
        
        var pollId = $block.attr('data-poll-id');
        
        console.log('Initializing poll block:', pollId, 'Found buttons:', $('.poll-vote-button, .poll-choice-button', $block).length);
        
        // Si l'utilisateur a déjà voté, désactiver les boutons
        if (pollId && hasVoted(pollId)) {
          var votedChoice = getVotedChoice(pollId);
          $('.poll-vote-button, .poll-choice-button', $block).prop('disabled', true);
          $('.poll-choice[data-choice-index="' + votedChoice + '"]', $block)
            .addClass('poll-choice-selected')
            .find('.poll-vote-button, .poll-choice-button')
            .addClass('poll-choice-selected-button');
          $block.attr('data-voted', 'true');
        }
      });
      
      // Utiliser la délégation d'événements pour capturer les clics sur les boutons
      // Cela fonctionne même si les boutons sont ajoutés dynamiquement
      $(context).off('click.poll-block', '.poll-vote-button, .poll-choice-button').on('click.poll-block', '.poll-vote-button, .poll-choice-button', function (e) {
          e.preventDefault();
          e.stopPropagation();
          
          console.log('Button clicked!');
          
          var $button = $(this);
          
          // Trouver le bloc parent
          var $block = $button.closest('.poll-block');
          
          if (!$block.length) {
            console.error('Poll block not found for button');
            return false;
          }
          
          // Vérifier si déjà voté
          if ($block.attr('data-voted') === 'true') {
            console.log('Already voted, ignoring click');
            return false;
          }
          
          // Récupérer les données depuis les attributs data
          var pollId = $button.attr('data-poll-id') || $block.attr('data-poll-id');
          var choiceIndex = parseInt($button.attr('data-choice-index'), 10);
          
          console.log('Button data:', {
            pollId: pollId,
            choiceIndex: choiceIndex,
            buttonText: $button.text(),
            buttonElement: $button[0],
            hasDataPollId: $button.attr('data-poll-id'),
            hasDataChoiceIndex: $button.attr('data-choice-index')
          });
          
          // Vérifier que les données sont présentes
          if (!pollId || isNaN(choiceIndex)) {
            console.error('Poll vote: Missing data', {
              pollId: pollId,
              choiceIndex: choiceIndex,
              button: $button[0],
              buttonAttrs: {
                'data-poll-id': $button.attr('data-poll-id'),
                'data-choice-index': $button.attr('data-choice-index')
              },
              blockPollId: $block.attr('data-poll-id')
            });
            alert('Erreur: Données manquantes pour le vote.');
            return false;
          }
          
          console.log('Poll vote attempt:', { pollId: pollId, choiceIndex: choiceIndex });
          
          // Désactiver tous les boutons pendant le vote
          $('.poll-vote-button, .poll-choice-button', $block).prop('disabled', true);
          
          // Ajouter une classe de chargement
          $block.addClass('poll-voting');
          
          // Envoyer la requête AJAX
          $.ajax({
            url: '/api/poll/vote',
            method: 'POST',
            data: {
              poll_id: pollId,
              choice_index: choiceIndex
            },
            dataType: 'json',
            headers: {
              'X-Requested-With': 'XMLHttpRequest'
            },
            success: function (response) {
              console.log('Poll vote response:', response);
              if (response && response.success) {
                updatePollResults($block, response.results, choiceIndex);
              } else {
                alert('Erreur: ' + (response && response.message ? response.message : 'Une erreur est survenue'));
                console.error('Poll vote response error:', response);
                // Réactiver les boutons en cas d'erreur
                $block.removeClass('poll-voting');
                $('.poll-vote-button, .poll-choice-button', $block).prop('disabled', false);
              }
            },
            error: function (xhr, status, error) {
              alert('Erreur lors du vote. Veuillez réessayer.');
              console.error('Poll vote error:', {
                status: status,
                error: error,
                responseText: xhr.responseText,
                statusCode: xhr.status
              });
              // Réactiver les boutons en cas d'erreur
              $block.removeClass('poll-voting');
              $('.poll-vote-button, .poll-choice-button', $block).prop('disabled', false);
            },
            complete: function () {
              $block.removeClass('poll-voting');
            }
          });
          
          return false;
        });
    }
  };

})(jQuery, Drupal);
