(function ($) {
  'use strict';
  Drupal.behaviors.fatNavigation = {
    attach: function (context, settings) {
      var toggleMenu = this.toggleMenu;
      var menuToggles = $('.main-menu__expand');

      menuToggles.once().on({
        click: function (event) {
          var parentMenuItem = $(this).parent('li');
          toggleMenu(event, parentMenuItem);
        },
        keypress: function (event) {
          var parentMenuItem = $(this).parent('li');
          // If key is not TAB (fix for Firefox 60.x.xesr).
          if (event.keyCode != 9) {
            toggleMenu(event, parentMenuItem);
          }
        }
      });
    },

    toggleMenu: function (event, parentMenuItem) {
      event.preventDefault();
      var childMenuItems = parentMenuItem.find('li');
      parentMenuItem.toggleClass('is-open');
      childMenuItems.toggle();
    }
  };
}(jQuery));
