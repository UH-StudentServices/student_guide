/* global Drupal, jQuery, window */

(function ($) {
  'use strict';
  Drupal.behaviors.responsiveNavigation = {
    attach: function(context, settings) {
      var mainUl = $('.main-menu > ul'),
          menuToggle = $('#menu-toggle'),
          breakpoints = settings.breakpoints,
          avatar = $('.avatar'),
          avatarMobileMenu = $('.block-language .links'),
          avatarDesktopMenu = $('.main-menu .menu');

      // toggle mobile menu
      menuToggle.once().on('click', function(e) {
        e.preventDefault();
        $(this).toggleClass('active');
        mainUl.toggleClass('is-open').toggleClass('is-slidein');
      });

      // Check media queries and add listener
      if (typeof matchMedia !== 'undefined') {
        var mq = window.matchMedia(breakpoints['small']);
        mq.addListener(moveAvatar);
        moveAvatar(mq);
      }

      // Move avatar if media query matches
      function moveAvatar(mq) {
        mq.matches ? avatarDesktopMenu.append(avatar) : avatarMobileMenu.append(avatar);  
      }

    }
  };
}(jQuery));
