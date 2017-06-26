(function ($) {
  'use strict';

  // Hides office hours that do not match the active degree programme.
  Drupal.behaviors.filterOfficeHours = {
    attach: function (context, settings) {
      var degreeProgramme = $.cookie('Drupal.visitor.degree_programme');

      $('.office-hours').once().each(function () {
        // When degree programme is available filter out certain items.
        if (degreeProgramme) {
          var tids = $(this).attr('data-degree-programme-tids').split(',');
          if ($.inArray(degreeProgramme, tids) === -1) {
            $(this).hide();
          }
        }
        else {
          // When no degree programme available, then hide everything.
          $(this).hide();
        }
      });
    }
  };
}(jQuery));
