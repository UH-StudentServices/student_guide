(function ($) {
  'use strict';
  Drupal.behaviors.degreeProgrammeSwitcher = {
    attach: function(context, settings) {
      var degreeProgrammeSwitcher = '.degree-programme-switcher',
          container = $('.degree-programme-switcher'),
          header = $('.degree-programme-switcher__header', degreeProgrammeSwitcher),
          dropdown = $('.degree-programme-switcher__dropdown', degreeProgrammeSwitcher),
          toggle = $('.degree-programme-switcher__toggle', degreeProgrammeSwitcher),
          searchInput = $('input[name="name"]', degreeProgrammeSwitcher),
          toggleClass = 'collapsed',
          toggleTextClosed = Drupal.t('Change'),
          toggleTextOpen = Drupal.t('Close'),
          breakpoints = settings.breakpoints;

      // toggle collapsed when clicking header
      header.once().on('click', function() {
        container.toggleClass(toggleClass);
        $('body').toggleClass('no-scroll-mobile');
        toggle.text(container.hasClass(toggleClass) ? toggleTextOpen : toggleTextClosed);

        if (window.matchMedia(breakpoints['small']).matches) {
          searchInput.focus();
        }
      });

      // close when clicking outside
      $(document).once().on('click', function(e) {
        var clickedOutside = $(e.target).parents(degreeProgrammeSwitcher).length == 0;
        if (container.hasClass(toggleClass) && clickedOutside) {
          container.removeClass(toggleClass);
          $('body').removeClass('no-scroll-mobile');
          toggle.text(container.hasClass(toggleClass) ? toggleTextOpen : toggleTextClosed);
        }
      });

      dropdown.prepend('<input type="text" name="filter" class="degree-programme-switcher__search">');
      $('.degree-programme-switcher__search').searchFilter({ targetSelector: ".list-of-links__link", charCount: 2});
    }
  };
}(jQuery));
