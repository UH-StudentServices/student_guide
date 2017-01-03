(function ($) {
  'use strict';

  Drupal.behaviors.feedbackForm = {
    attach: function(context, settings) {
      $('.feedback-form__toggle', '.feedback-form').once().on('click', function() {
        $(this).toggleClass('collapsed').next('form').toggleClass('visually-hidden');
      });
    }
  };

}(jQuery));
