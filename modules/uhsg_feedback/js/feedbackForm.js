(function ($) {
  'use strict';

  Drupal.behaviors.feedbackForm = {
    attach: function () {
      $('.feedback-form__toggle', '.feedback-form').once().on('click', function () {
        $(this).toggleClass('active').next('#feedback-form__content').children('form').toggleClass('visually-hidden');
        $(this).hasClass('active') ? // eslint-disable-line no-unused-expressions
          $(this).children('.feedback-form__icon').removeClass('icon--chat icon-2x').addClass('icon--remove') :
          $(this).children('.feedback-form__icon').removeClass('icon--remove').addClass('icon--chat icon-2x');
      });
      $('#edit-field-feedback-respond-value', '.feedback-form').prop('checked') === false && $('.form-item-mail', '.feedback-form').once().addClass('visually-hidden'); // eslint-disable-line no-unused-expressions
      $('#edit-field-feedback-respond-value', '.feedback-form').once().change(function () {
        $(this).prop('checked') === false && $('#edit-mail', '.feedback-form').val(''); // eslint-disable-line no-unused-expressions
        $('.form-item-mail', '.feedback-form').toggleClass('visually-hidden');
      });
    }
  };

}(jQuery));
