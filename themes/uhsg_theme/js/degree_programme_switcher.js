(function ($) {
  'use strict';
  Drupal.behaviors.degreeProgrammeSwitcher = {
    attach: function(context, settings) {
      var degreeProgrammeSwitcher = '.degree-programme-switcher',
          container = $('.degree-programme-switcher'),
          header = $('.degree-programme-switcher__header', degreeProgrammeSwitcher),
          dropdown = $('.degree-programme-switcher__dropdown', degreeProgrammeSwitcher),
          toggle = $('.degree-programme-switcher__toggle', degreeProgrammeSwitcher),
          filterInput = $('.degree-programme-switcher__filter input', degreeProgrammeSwitcher),
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
          filterInput.focus();
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

      filterInput.degreeProgrammeFilter({
        container: ".view-degree-programmes",
        item: ".list-of-links__link",
        groupingTitle: ".view-list-title"
      });
    }
  };
}(jQuery));
