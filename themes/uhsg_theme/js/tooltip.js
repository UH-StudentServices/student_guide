(function ($) {
  'use strict';
  Drupal.behaviors.tooltip = {
    attach: function (context, settings) {
      var tooltip = $('.tooltip');

      tooltip.on('click', function (event) {
        event.preventDefault();
      });

      tooltip.tooltip({
        open: function (e, ui) {
          tooltip.not(this).tooltip('close');
        }
      });

      $(document).on('touchstart click', '.ui-tooltip-content', function (event) {
        tooltip.tooltip('close');
      });
    }
  };
}(jQuery));
