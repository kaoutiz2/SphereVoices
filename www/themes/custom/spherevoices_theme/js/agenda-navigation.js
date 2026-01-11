(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.agendaMonthNav = {
    attach: function (context, settings) {
      // Ajouter une navigation par mois au-dessus du formulaire
      var $form = $('.view-id-agenda .views-exposed-form', context).once('month-nav');
      
      if ($form.length) {
        // Récupérer le mois actuel depuis l'URL ou utiliser le mois courant
        var urlParams = new URLSearchParams(window.location.search);
        var currentMonth = urlParams.get('month');
        
        if (!currentMonth) {
          var today = new Date();
          currentMonth = today.getFullYear() + '-' + String(today.getMonth() + 1).padStart(2, '0');
        }
        
        var monthInput = $form.find('input[name="month"]');
        if (monthInput.length && !monthInput.val()) {
          monthInput.val(currentMonth);
        }
        
        // Créer la navigation par mois
        var $monthNav = $('<div class="agenda-month-selector"></div>');
        
        // Calculer mois précédent et suivant
        var parts = currentMonth.split('-');
        var year = parseInt(parts[0]);
        var month = parseInt(parts[1]);
        
        var prevDate = new Date(year, month - 2, 1);
        var nextDate = new Date(year, month, 1);
        
        var prevMonth = prevDate.getFullYear() + '-' + String(prevDate.getMonth() + 1).padStart(2, '0');
        var nextMonth = nextDate.getFullYear() + '-' + String(nextDate.getMonth() + 1).padStart(2, '0');
        
        // Noms des mois en français
        var monthNames = [
          'Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin',
          'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'
        ];
        
        var currentMonthName = monthNames[month - 1] + ' ' + year;
        
        $monthNav.html(
          '<button type="button" class="month-nav-btn month-prev" data-month="' + prevMonth + '">' +
          '  ← ' + monthNames[prevDate.getMonth()] + ' ' + prevDate.getFullYear() +
          '</button>' +
          '<span class="month-current">' + currentMonthName + '</span>' +
          '<button type="button" class="month-nav-btn month-next" data-month="' + nextMonth + '">' +
          '  ' + monthNames[nextDate.getMonth()] + ' ' + nextDate.getFullYear() + ' →' +
          '</button>'
        );
        
        // Insérer avant le formulaire
        $form.before($monthNav);
        
        // Gérer les clics sur les boutons
        $monthNav.find('.month-nav-btn').on('click', function() {
          var targetMonth = $(this).data('month');
          var url = new URL(window.location.href);
          url.searchParams.set('month', targetMonth);
          window.location.href = url.toString();
        });
      }
    }
  };

})(jQuery, Drupal);

