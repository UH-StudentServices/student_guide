(function ($) {
  'use strict';

  Drupal.behaviors.degreeProgrammeSwitcher = {
    attach: function (context, settings) {
      var triggerToggle = this.triggerToggle;
      var degreeProgrammeSwitcher = '.degree-programme-switcher';
      var container = $('.degree-programme-switcher');
      var header = $('.degree-programme-switcher__header', degreeProgrammeSwitcher);
      var toggle = $('.degree-programme-switcher__toggle', degreeProgrammeSwitcher);
      var dropdown = $('.degree-programme-switcher__dropdown', degreeProgrammeSwitcher);
      var filterInput = $('.degree-programme-switcher__filter input', degreeProgrammeSwitcher);
      var toggleClass = 'collapsed';
      var toggleIconClosed = 'icon--caret-down';
      var toggleIconOpen = 'icon--caret-up';
      var breakpoints = settings.breakpoints;

      // Toggle collapsed when click or keypress on header
      header.once().on({
        click: function (event) {
          triggerToggle(event, container, header, dropdown, toggleClass, toggle, toggleIconClosed, toggleIconOpen, breakpoints, filterInput);
        }
      });

      // Close when clicking outside
      $(document).once().on('click', function (e) {
        var clickedOutside = $(e.target).parents(degreeProgrammeSwitcher).length === 0;
        if (container.hasClass(toggleClass) && clickedOutside) {
          container.removeClass(toggleClass);
          $('body').removeClass('no-scroll-mobile');
          toggle.toggleClass(toggleIconClosed);
          toggle.toggleClass(toggleIconOpen);
        }
      });

      // Refresh view after adding to item to my degree programmes
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
        container: '.degree-programme-switcher__list',
        item: '.list-of-links__link',
        groupingTitle: '.view-subtitle',
        ariaLive: '.degree-programme-switcher__filter-messages',
      });

    },

    triggerToggle: function (event, container, header, dropdown, toggleClass, toggle, toggleIconClosed, toggleIconOpen, breakpoints, filterInput) {
      event.preventDefault();
      container.toggleClass(toggleClass);
      $('body').toggleClass('no-scroll-mobile');
      toggle.toggleClass(toggleIconClosed);
      toggle.toggleClass(toggleIconOpen);

      if (container.hasClass(toggleClass)) {
        header.attr('aria-expanded', 'true');
        dropdown.attr('hidden', null);
      }
      else {
        header.attr('aria-expanded', 'false');
        dropdown.attr('hidden', '');
      }

      if (window.matchMedia(breakpoints['small']).matches) {
        filterInput.focus();
      }
    }
  };

}(jQuery));
