(function ($) {
  'use strict';
  Drupal.behaviors.otherEducationProviderSwitcher = {
    attach: function (context, settings) {
      var triggerToggle = this.triggerToggle;
      var otherEducationProviderSwitcher = '.other-education-provider-switcher';
      var container = $('.other-education-provider-switcher');
      var header = $('.other-education-provider-switcher__header', otherEducationProviderSwitcher);
      var toggle = $('.other-education-provider-switcher__toggle', otherEducationProviderSwitcher);
      var dropdown = $('.other-education-provider-switcher__dropdown', otherEducationProviderSwitcher);
      var toggleClass = 'collapsed';
      var toggleIconClosed = 'icon--caret-down';
      var toggleIconOpen = 'icon--caret-up';
      var resetButton = $('.button--reset', otherEducationProviderSwitcher);

      // Toggle collapsed when click or keypress on header
      header.once().on({
        click: function (event) {
          triggerToggle(event, container, header, dropdown, toggleClass, toggle, toggleIconClosed, toggleIconOpen);
        }
      });

      // Close when clicking or focusing outside
      $(document).once('other_education_provider_switcher_focus_out').on('click focusin', function (e) {
        var clickedOutside = $(e.target).parents(otherEducationProviderSwitcher).length === 0;
        if (container.hasClass(toggleClass) && clickedOutside) {
          container.removeClass(toggleClass);
          $('body').removeClass('no-scroll-mobile');
          toggle.toggleClass(toggleIconClosed);
          toggle.toggleClass(toggleIconOpen);
        }
      });

      // Reset button.
      resetButton.once().on('click', function(e) {
        var reset_uri = $(this).attr('data-href');
        if (reset_uri) {
          window.location.href = reset_uri;
        }
      });

    },

    triggerToggle: function (event, container, header, dropdown, toggleClass, toggle, toggleIconClosed, toggleIconOpen) {
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
    }
  };
}(jQuery));
