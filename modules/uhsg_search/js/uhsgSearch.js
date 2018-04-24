(function ($) {
  'use strict';
  Drupal.behaviors.requireSearchText = {
    attach: function (context, settings) {
      var searchField = $('#edit-search-api-fulltext');
      var searchButton = $('#edit-submit-search');

      if (!searchField.val()) {
        searchButton.prop('disabled', true);
      }

      searchField.keyup(function() {
        searchButton.prop('disabled', !$(this).val());
      });
    }
  }
}(jQuery));
