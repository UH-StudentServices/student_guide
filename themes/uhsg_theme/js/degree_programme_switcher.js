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
          toggleTextClosed = Drupal.t('Change'),
          toggleTextOpen = Drupal.t('Close');

      // toggle collapsed when clicking header
      header.once().on('click', function() {
        container.toggleClass(toggleClass);
        toggle.text(container.hasClass(toggleClass) ? toggleTextOpen : toggleTextClosed);
        searchInput.focus();
      });

      // close when clicking outside
      $(document).once().on('click', function(e) {
        var clickedOutside = $(e.target).parents(degreeProgrammeSwitcher).length == 0;
        if (container.hasClass(toggleClass) && clickedOutside) {
          container.removeClass(toggleClass);
          toggle.text(container.hasClass(toggleClass) ? toggleTextOpen : toggleTextClosed);
        }
      })

    }
  };
}(jQuery));
