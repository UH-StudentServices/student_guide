(function ($) {
  'use strict';
  Drupal.behaviors.anchor = {
    attach: function (context, settings) {
      $('a#up-anchor, a.index__link').once().click(function () {
        var target = $(this.hash).length > 0 ? $(this.hash).offset().top : 0;
        $('html, body').animate({
          scrollTop: target
        }, 'slow');
        return false;
      });
    }
  };
}(jQuery));
