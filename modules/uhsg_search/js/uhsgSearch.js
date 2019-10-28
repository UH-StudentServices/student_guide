(function ($) {
  'use strict';
  Drupal.behaviors.requireSearchText = {
    attach: function (context, settings) {
      var self = this;
      var searchField = $('#edit-search-api-fulltext');
      var searchButton = $('#edit-submit-search');

      if (!searchField.val()) {
        searchButton.prop('disabled', true);
      }

      searchField.keyup(function(e) {
        self.handleKeyUp(e, searchField, searchButton);
      });
    },

    handleKeyUp: function(e, searchField, searchButton) {
      var empty = !searchField.val();
      searchButton.prop('disabled', empty);
      if (empty && e.which == 13) {
        var originalPlaceholder = searchField.prop('placeholder');
        searchField.prop('placeholder', Drupal.t('Required field'));
        setTimeout(function () {
          searchField.prop('placeholder', originalPlaceholder);
        }, 1000);
      }
      searchField.val(searchField.val().toLowerCase());
    }
  }
}(jQuery));
