(function ($) {
  'use strict';
  Drupal.behaviors.uhsg_search_my_searches = {
    attach: function(context, settings) {

      var searchInput = $('#views-exposed-form-search-block-1 input[name="search_api_fulltext"]');
      var searchSubmit = $('#views-exposed-form-search-block-1 .form-submit:not(".button--reset")');
      var mySearches = $.cookie('my_searches') ? JSON.parse($.cookie('my_searches')) : [];
      var empty = $('.view-empty', '.view-search').length;
      var maxLatestSearches = 4;

      // Store submitted value in a cookie.
      if (searchInput.val() && !empty) {

        // Avoid duplicates.
        var dupe = mySearches.find(function(item) {
          return item == searchInput.val();
        });

        if (!dupe) {
          mySearches.unshift(searchInput.val());

          if (mySearches.length > maxLatestSearches) {
            mySearches.pop();
          }

          // Store my searches in a cookie.
          $.cookie('my_searches', JSON.stringify(mySearches), { expires: 999 });
        }
      }

      // Display searches in a list.
      if (mySearches.length) {
        var content = '';
        mySearches.map(function(value) {
          content += '<li class="list-of-links__link button--action-before icon--search theme-transparent">' + value + '</li>';
        });

        var title = '<h3>' + Drupal.t('My Searches') + '</h3>';
        $('#my-searches').empty();
        $('#my-searches').append(title + '<ul class="list-of-links">' + content + '</ul>');
        $('#my-searches').append('<button class="button--reset">' + Drupal.t('Remove') + '</button><i class="icon--remove"></i>');

        // Enable search when clicking one of my searches items.
        $('#my-searches li').on('click', function() {
          searchInput.val($(this).text());
          searchSubmit.click();
        });

        // Empty my searches and delete the cookie.
        $('.button--reset', '#my-searches').on('click', function() {
          $.removeCookie('my_searches');
          $('#my-searches').empty();
        });
      }
    }
  };
}(jQuery));
