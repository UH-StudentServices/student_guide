(function ($) {
  'use strict';
  Drupal.behaviors.otherEducationProviderSwitcher = {
    attach: function (context, settings) {
      var triggerToggle = this.triggerToggle;
      var otherEducationProviderSwitcher = '.other-education-provider-switcher';
      var container = $('.other-education-provider-switcher');
      var header = $('.other-education-provider-switcher__header', otherEducationProviderSwitcher);
      var toggle = $('.other-education-provider-switcher__toggle', otherEducationProviderSwitcher);
      var toggleClass = 'collapsed';
      var toggleIconClosed = 'icon--caret-down';
      var toggleIconOpen = 'icon--caret-up';

      // Toggle collapsed when click or keypress on header
      header.once().on({
        click: function (event) {
          triggerToggle(event, container, toggleClass, toggle, toggleIconClosed, toggleIconOpen);
        },
        keypress: function (event) {
          triggerToggle(event, container, toggleClass, toggle, toggleIconClosed, toggleIconOpen);
        }
      });

      // Close when clicking outside
      $(document).once().on('click', function (e) {
        var clickedOutside = $(e.target).parents(otherEducationProviderSwitcher).length === 0;
        if (container.hasClass(toggleClass) && clickedOutside) {
          container.removeClass(toggleClass);
          $('body').removeClass('no-scroll-mobile');
          toggle.toggleClass(toggleIconClosed);
          toggle.toggleClass(toggleIconOpen);
        }
      });
    },

    triggerToggle: function (event, container, toggleClass, toggle, toggleIconClosed, toggleIconOpen) {
      event.preventDefault();
      container.toggleClass(toggleClass);
      $('body').toggleClass('no-scroll-mobile');
      toggle.toggleClass(toggleIconClosed);
      toggle.toggleClass(toggleIconOpen);
    }
  };
}(jQuery));
