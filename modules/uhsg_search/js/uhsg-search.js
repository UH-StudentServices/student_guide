(function ($) {
  'use strict';
  Drupal.behaviors.uhsg_search_reset = {
    attach: function(context, settings) {

      // Exposed filter reset redirects user to 404 page on AJAX view when
      // placed as a block (https://www.drupal.org/node/2820347). Until core is
      // fixed, using location.reload() to reload the current page to reset the
      // form.
      $('.view-search .views-exposed-form input.button--reset').click(function (e) {
        e.preventDefault();
    	  location.reload();
      })
    }
  };
}(jQuery));
