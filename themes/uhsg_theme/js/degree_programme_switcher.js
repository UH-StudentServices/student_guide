(function ($) {
  'use strict';
  Drupal.behaviors.degreeProgrammeSwitcher = {
    attach: function(context, settings) {
      var header = $('.degree-programme-switcher__header'),
          container = $('.degree-programme-switcher__container'),
          toggle = $('.degree-programme-switcher__toggle'),
          toggleClass = 'collapsed',
          toggleTextOpen = Drupal.t('Change'),
          toggleTextClosed = Drupal.t('Close');

      header.once().on('click', function() {
        container.toggleClass(toggleClass);
        toggle.text(container.hasClass(toggleClass) ? toggleTextOpen : toggleTextClosed);
      });
    }
  };
}(jQuery));
