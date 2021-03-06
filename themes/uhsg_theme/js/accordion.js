(function ($) {
  'use strict';
  Drupal.behaviors.accordion = {
    attach: function (context, settings) {
      $('.accordion__title', context).once().on('click', function () {
        $(this).toggleClass('is-active').next('.accordion__content').toggleClass('visually-hidden');
      });
      $('.accordion__item', context).once().on('keypress', function (event) {
        // If key is not TAB (fix for Firefox 60.x.xesr).
        if (event.keyCode != 9) {
          event.preventDefault();
          $(this).children('.accordion__title').first().toggleClass('is-active').next('.accordion__content').toggleClass('visually-hidden');
        }
      });
    }
  };
}(jQuery));
