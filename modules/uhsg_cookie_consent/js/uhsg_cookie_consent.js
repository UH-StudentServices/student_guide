(function ($) {
  'use strict';
  Drupal.behaviors.uhsg_cookie_consent = {
    attach: function(context, settings) {
    	window.cookieconsent_options = settings.uhsg_cookie_consent.options;
    }
  };
}(jQuery));
