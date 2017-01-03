(function ($) {
  'use strict';

  Drupal.behaviors.feedbackForm = {
    attach: function(context, settings) {
      $('.feedback-form__toggle', '.feedback-form').once().on('click', function() {
        $(this).toggleClass('active').next('#feedback-form__content').children('form').toggleClass('visually-hidden');
        $(this).hasClass('active') ?
          $(this).children('.feedback-form__icon').removeClass('icon--chat icon-2x').addClass('icon--remove') :
          $(this).children('.feedback-form__icon').removeClass('icon--remove').addClass('icon--chat icon-2x');
      });
    }
  };

}(jQuery));
