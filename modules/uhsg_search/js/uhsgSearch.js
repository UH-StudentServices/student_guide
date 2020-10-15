(function ($) {
  'use strict';

  Drupal.behaviors.requireSearchText = {
    attach: function (context, settings) {
      var self = this;
      var searchField = $('#edit-search-api-fulltext');
      var searchButton = $('#edit-submit-search');

      if (!searchField.val()) {
        searchButton.prop('aria-disabled', true);
      }

      searchButton.on('click', function(e) {
        self.handleSubmit(e, searchField, searchButton);
      });

      searchField.on('keyup', function(e) {
        self.handleKeyUp(e, searchField, searchButton);
      });
    },

    handleSubmit: function(e, searchField, searchButton) {
      var empty = !searchField.val();
      if (empty) {
        var originalPlaceholder = searchField.prop('placeholder');
        searchField.prop('placeholder', Drupal.t('Required field'));
        setTimeout(function () {
          searchField.prop('placeholder', originalPlaceholder);
        }, 1000);
      }
    },

    handleKeyUp: function(e, searchField, searchButton) {
      var empty = !searchField.val();
      searchButton.prop('aria-disabled', empty);
      searchField.val(searchField.val().toLowerCase());
    }
  }

}(jQuery));
