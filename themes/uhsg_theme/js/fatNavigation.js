(function ($) {
  'use strict';
  Drupal.behaviors.fatNavigation = {
    attach: function (context, settings) {
      var menuToggles = $('.main-menu__expand');
      var breakpoints = settings.breakpoints;

      menuToggles.once().on('click', function (e) {
        e.preventDefault();
        var parentMenuItem = $(this).parent('li');
        var siblingMenuItems = parentMenuItem.siblings('li');
        var childMenuItems = parentMenuItem.find('li');
        parentMenuItem.toggleClass('is-open');
        childMenuItems.toggle();

        // Hide sibling menu items on larger screens.
        if (typeof matchMedia !== 'undefined') {
          var mq = window.matchMedia(breakpoints['small']);
          if (mq.matches) {
            siblingMenuItems.toggle();
          }
        }
      });
    }
  };
}(jQuery));
