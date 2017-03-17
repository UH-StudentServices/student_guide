/* global Drupal, jQuery */

(function ($) {
  'use strict';
  Drupal.behaviors.accordion = {
    attach: function(context) {
      $('.accordion__title', context).once().on('click', function() {
        $(this).toggleClass('is-active').next('.accordion__content').toggleClass('visually-hidden');
      });
    }
  };
}(jQuery));
