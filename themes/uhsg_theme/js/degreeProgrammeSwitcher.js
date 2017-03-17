(function ($) {
  'use strict';
  Drupal.behaviors.degreeProgrammeSwitcher = {
    attach: function (context, settings) {
      var degreeProgrammeSwitcher = '.degree-programme-switcher';
      var container = $('.degree-programme-switcher');
      var header = $('.degree-programme-switcher__header', degreeProgrammeSwitcher);
      var toggle = $('.degree-programme-switcher__toggle', degreeProgrammeSwitcher);
      var filterInput = $('.degree-programme-switcher__filter input', degreeProgrammeSwitcher);
      var toggleClass = 'collapsed';
      var toggleTextClosed = Drupal.t('Change');
      var toggleTextOpen = Drupal.t('Close');
      var breakpoints = settings.breakpoints;

      // toggle collapsed when clicking header
      header.once().on('click', function () {
        container.toggleClass(toggleClass);
        $('body').toggleClass('no-scroll-mobile');
        toggle.text(container.hasClass(toggleClass) ? toggleTextOpen : toggleTextClosed);

        if (window.matchMedia(breakpoints['small']).matches) {
          filterInput.focus();
        }
      });

      // close when clicking outside
      $(document).once().on('click', function (e) {
        var clickedOutside = $(e.target).parents(degreeProgrammeSwitcher).length === 0;
        if (container.hasClass(toggleClass) && clickedOutside) {
          container.removeClass(toggleClass);
          $('body').removeClass('no-scroll-mobile');
          toggle.text(container.hasClass(toggleClass) ? toggleTextOpen : toggleTextClosed);
        }
      });

      // refresh view after adding to item to my degree programmes
      $.each(Drupal.views.instances, function (index, element) {
        if (element.settings.view_name === 'degree_programmes') {
          $(document).ajaxSuccess(function (event, request, settings) {
            if (~settings.url.indexOf('flag')) {
              $('.js-view-dom-id-' + element.settings.view_dom_id).trigger('RefreshView');
            }
          });
        }
      });

      // Apply view filtering to input
      filterInput.degreeProgrammeFilter({
        container: '.view-degree-programmes',
        item: '.list-of-links__link',
        groupingTitle: '.view-subtitle'
      });

    }
  };
}(jQuery));
