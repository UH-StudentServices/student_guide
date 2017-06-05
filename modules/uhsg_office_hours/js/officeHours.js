(function ($) {
  'use strict';
  Drupal.behaviors.filterOfficeHours = {
    attach: function (context, settings) {
      var degreeProgramme = $.cookie('Drupal.visitor.degree_programme');

      if (degreeProgramme) {
        $('.office-hours').once().each(function () {
          if ($(this).attr('data-degree-programme-tid') !== degreeProgramme) {
            $(this).hide();
          }
        });
      }
    }
  };
}(jQuery));
