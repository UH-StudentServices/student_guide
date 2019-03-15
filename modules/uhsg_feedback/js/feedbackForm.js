(function ($) {
  'use strict';

  Drupal.behaviors.feedbackForm = {
    attach: function (context, settings) {
      var triggerToggle = this.triggerToggle;

      $('.feedback-form__toggle', '.feedback-form').once().on({
        click: function (event) {
          triggerToggle(event, $(this));
        },
        keypress: function (event) {
          triggerToggle(event, $(this));
        },
      });
      $('#edit-field-feedback-respond-value', '.feedback-form').prop('checked') === false && $('.form-item-mail', '.feedback-form').once().addClass('visually-hidden'); // eslint-disable-line no-unused-expressions
      $('#edit-field-feedback-respond-value', '.feedback-form').once().change(function () {
        $(this).prop('checked') === false && $('#edit-mail', '.feedback-form').val(''); // eslint-disable-line no-unused-expressions
        $('.form-item-mail', '.feedback-form').toggleClass('visually-hidden');
      });
    },

    triggerToggle: function (event, element) {
      event.preventDefault();
      element.toggleClass('active').next('#feedback-form__content').children('form').toggleClass('visually-hidden');
      element.hasClass('active') ? // eslint-disable-line no-unused-expressions
        element.children('.feedback-form__icon').removeClass('icon--chat icon-2x').addClass('icon--remove') :
        element.children('.feedback-form__icon').removeClass('icon--remove').addClass('icon--chat icon-2x');
    }
  };

}(jQuery));
