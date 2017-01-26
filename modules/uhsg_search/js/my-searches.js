(function ($) {
  'use strict';
  Drupal.behaviors.uhsg_search_my_searches = {
    attach: function(context, settings) {

      var searchInput = $('#views-exposed-form-search-block-1 input[name="search_api_fulltext"]');
      var my_searches = $.cookie('my_searches') ? JSON.parse($.cookie('my_searches')) : [];

      // store submitted value in cookie
      if (searchInput.val()) {

        // no duplicates
        var dupe = my_searches.find(function(item) {
          return item == searchInput.val() ? true : false;
        });

        if (!dupe) {
          my_searches.unshift(searchInput.val());
          // store only 4 latest searches
          if (my_searches.length > 4) {
            my_searches.pop();
          }

          // store my searches in cookie
          $.cookie('my_searches', JSON.stringify(my_searches), { expires: 999 });
        }
      }

      // display searches in a list
      if (my_searches.length) {
        var content = '';
        my_searches.map(function(value) {
          content += '<li class="list-of-links__link button--action-before icon--search theme-transparent">' + value + '</li>';
        });

        var title = '<h3>' + Drupal.t('My Searches') + '</h3>';
        $('#my-searches').empty();
        $('#my-searches').append(title + '<ul class="list-of-links">' + content + '</ul>');
        $('#my-searches').append('<button class="button--reset">' + Drupal.t('Remove') + '</button><i class="icon--remove"></i>');

        // enable search when clicking one of my searches items
        $('#my-searches li').on('click', function() {
          searchInput.val($(this).text());
          searchSubmit.click();
        });

        // Remove my searches and delete cookie
        $('.button--reset', '#my-searches').on('click', function() {
          $.removeCookie('my_searches');
          $('#my-searches').empty();
        });
      }
    }
  };
}(jQuery));
