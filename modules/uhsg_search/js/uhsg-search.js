(function ($) {
  'use strict';
  Drupal.behaviors.uhsg_search_reset = {
    attach: function(context, settings) {

      // Exposed filter reset redirects user to 404 page on AJAX view when
      // placed as a block (https://www.drupal.org/node/2820347). Until core is
      // fixed, using location.reload() to reload the current page to reset the
      // form.
    	$('#views-exposed-form-search-block-1 input.button--reset').click(function (e) {
    	  e.preventDefault();
    	  location.reload();
      });

      //retrieve searches from cookie and display them
      if ($.cookie('my_searches')) {
        var my_searches = JSON.parse($.cookie('my_searches'));
        my_searches.reverse();
        var content = my_searches.map(function(value) {
          return '<li class="list-of-links__link button--action-before icon--search theme-transparent">' + value + '</li>';
        });
        var title = '<h3>' + Drupal.t('My Searches') + '</h3>';
        $('.my-searches').empty();
        $('.my-searches').append(title + '<ul class="list-of-links">' + content + '</ul>');
      }

      // store searches in cookie
      $('#edit-submit-search', '#views-exposed-form-search-block-1').on('click', function() {
        if ($.cookie('my_searches')) {
          var my_searches = JSON.parse($.cookie('my_searches'));
        }
        else {
          var my_searches = [];
        }  
        console.log(my_searches);
        my_searches.push($('#edit-search-api-fulltext').val());
        if (my_searches.length > 4) {
          my_searches.shift();
        }
        console.log(my_searches);

        $.cookie('my_searches', JSON.stringify(my_searches), { expires: 999 });
      });

    }
  };
}(jQuery));
