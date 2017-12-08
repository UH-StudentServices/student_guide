(function ($) {
  'use strict';
  Drupal.behaviors.fatNavigation = {
    attach: function (context, settings) {
      var menuToggles = $('.main-menu__expand');

      menuToggles.once().on('click', function (e) {
        e.preventDefault();
        var parentMenuItem = $(this).parent('li');
        var childMenuItems = parentMenuItem.find('li');
        parentMenuItem.toggleClass('is-open');
        childMenuItems.toggle();
      });
    }
  };
}(jQuery));
