(function ($) {
  'use strict';

  // Hides degree programme office hours that do not match the active degree programme.
  // General office hours are always displayed.
  Drupal.behaviors.filterOfficeHours = {
    attach: function (context, settings) {
      var selectedDegreeProgrammeTermId = $.cookie('Drupal.visitor.degree_programme');

      $('.degree-programme-office-hours .office-hours').once().each(function () {
        if (!selectedDegreeProgrammeTermId) {
          $(this).hide();
        }
        else {
          var termIds = $(this).attr('data-degree-programme-term-ids');
          if ($.inArray(selectedDegreeProgrammeTermId, termIds.split(',')) === -1) {
            $(this).hide();
          }
        }
      });

      var showDegreeProgrammeOfficeHoursAccordion = $('.degree-programme-office-hours .office-hours:visible').length > 0;

      if (!showDegreeProgrammeOfficeHoursAccordion) {
        $('.degree-programme-office-hours').hide();
      }
    }
  };
}(jQuery));
