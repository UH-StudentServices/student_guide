(function ($) {
  'use strict';
  Drupal.behaviors.degreeProgrammeSwitcher = {
    attach: function(context, settings) {
      var degreeProgrammeSwitcher = '.degree-programme-switcher',
          header = $('.degree-programme-switcher__header', degreeProgrammeSwitcher),
          container = $('.degree-programme-switcher__container', degreeProgrammeSwitcher),
          toggle = $('.degree-programme-switcher__toggle', degreeProgrammeSwitcher),
          searchInput = $('input[name="name"]', degreeProgrammeSwitcher),
          toggleClass = 'collapsed',
          toggleTextOpen = Drupal.t('Change'),
          toggleTextClosed = Drupal.t('Close');

      header.once().on('click', function() {
        container.toggleClass(toggleClass);
        toggle.text(container.hasClass(toggleClass) ? toggleTextOpen : toggleTextClosed);
        searchInput.focus();
      });
    }
  };
}(jQuery));
