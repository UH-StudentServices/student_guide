(function ($) {
  'use strict';
  Drupal.behaviors.degreeProgrammeSwitcher = {
    attach: function (context, settings) {
      var triggerToggle = this.triggerToggle;
      var degreeProgrammeSwitcher = '.degree-programme-switcher';
      var container = $('.degree-programme-switcher');
      var header = $('.degree-programme-switcher__header', degreeProgrammeSwitcher);
      var toggle = $('.degree-programme-switcher__toggle', degreeProgrammeSwitcher);
      var filterInput = $('.degree-programme-switcher__filter input', degreeProgrammeSwitcher);
      var toggleClass = 'collapsed';
      var toggleIconClosed = 'icon--caret-down';
      var toggleIconOpen = 'icon--caret-up';
      var breakpoints = settings.breakpoints;

      // Toggle collapsed when click or keypress on header
      header.once().on({
        click: function (event) {
          triggerToggle(event, container, toggleClass, toggle, toggleIconClosed, toggleIconOpen, breakpoints, filterInput);
        },
        keypress: function (event) {
          triggerToggle(event, container, toggleClass, toggle, toggleIconClosed, toggleIconOpen, breakpoints, filterInput);
        }
      });

      // Close when clicking or focusing outside
      $(document).once().on('click focusin', function (e) {
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
        container: '.view-degree-programmes',
        item: '.list-of-links__link',
        groupingTitle: '.view-subtitle'
      });

    },

    triggerToggle: function (event, container, toggleClass, toggle, toggleIconClosed, toggleIconOpen, breakpoints, filterInput) {
      // If key is not TAB (fix for Firefox 60.x.xesr).
      if (event.keyCode != 9) {
        event.preventDefault();
        container.toggleClass(toggleClass);
        $('body').toggleClass('no-scroll-mobile');
        toggle.toggleClass(toggleIconClosed);
        toggle.toggleClass(toggleIconOpen);

        if (window.matchMedia(breakpoints['small']).matches) {
          filterInput.focus();
        }
      }
    }
  };
}(jQuery));
