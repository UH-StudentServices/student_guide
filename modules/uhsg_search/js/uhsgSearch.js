(function ($) {
  'use strict';
  // Submit search when clicking suggestions.
  var oldSelect = Drupal.autocomplete.options.select;
  Drupal.autocomplete.options.select = function () {
    oldSelect.call(this);
    if ($(this).hasClass('ui-autocomplete-input')) {
      $(this).siblings('.form-actions').children('#edit-submit-search').trigger('click');
    }
  };
  // Autocomplete requires the user to click twice in iOS, fix that.
  Drupal.autocomplete.options.open = function () {
    $('.ui-autocomplete').off('menufocus hover mouseover');
  };
}(jQuery));
