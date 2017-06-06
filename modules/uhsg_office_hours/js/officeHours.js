(function ($) {
  'use strict';
  Drupal.behaviors.filterOfficeHours = {
    attach: function (context, settings) {
      var degreeProgramme = $.cookie('Drupal.visitor.degree_programme');

      if (degreeProgramme) {
        $('.office-hours').once().each(function () {
          var tids = $(this).attr('data-degree-programme-tids').split(',');

          if ($.inArray(degreeProgramme, tids) === -1) {
            $(this).hide();
          }
        });
      }
    }
  };
}(jQuery));
