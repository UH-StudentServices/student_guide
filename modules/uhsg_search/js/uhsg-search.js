(function ($) {
  // Override autocomplete select
  var oldSelect = Drupal.autocomplete.options.select;
  Drupal.autocomplete.options.select = function(event, ui) {
    oldSelect.call(this, event, ui);
    if ($(this).hasClass('ui-autocomplete-input')) {
      $(this).siblings('.form-actions').children('#edit-submit-search').trigger('click');
    }
  };
}(jQuery));
