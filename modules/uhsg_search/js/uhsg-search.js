(function ($) {
  // Submit search when clicking suggestions
  var oldSelect = Drupal.autocomplete.options.select;
  Drupal.autocomplete.options.select = function(event, ui) {
    oldSelect.call(this, event, ui);
    if ($(this).hasClass('ui-autocomplete-input')) {
      $(this).siblings('.form-actions').children('#edit-submit-search').trigger('click');
    }
  };
  // autocomlete requires the user to click twice in iOS, fix that
  Drupal.autocomplete.options.open = function (event, ui) {
    console.log('open jepajee.');
    $('.ui-autocomplete').off('menufocus hover mouseover');
  };
}(jQuery));
