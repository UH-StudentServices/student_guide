(function ($) {
  'use strict';
  Drupal.behaviors.uhsgCookieConsent = {
    attach: function (context, settings) {
      window.cookieconsent_options = settings.uhsg_cookie_consent.options;
    }
  };
}(jQuery));
