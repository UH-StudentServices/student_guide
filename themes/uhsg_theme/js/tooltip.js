(function ($) {
  'use strict';
  Drupal.behaviors.tooltip = {
    attach: function (context, settings) {
      $(document).tooltip({tooltipClass: "description"});
    }
  };
}(jQuery));
