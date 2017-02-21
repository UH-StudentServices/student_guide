(function ($) {
  'use strict';
  Drupal.behaviors.anchor = {
    attach: function(context, settings) {
      $("a#up-anchor").once().click(function () {
        $('html, body').animate({ scrollTop: 0 }, "slow");
        return false;
      });
    }
  };
}(jQuery));
