(function ($) {
  'use strict';
  Drupal.behaviors.responsiveNavigation = {
    attach: function (context, settings) {
      var mainUl = $('.main-menu > ul');
      var menuToggle = $('#menu-toggle');
      var breakpoints = settings.breakpoints;
      var avatar = $('.avatar');
      var avatarMobileMenu = $('.block-language .links');
      var avatarDesktopMenu = $('.main-menu .menu');

      // toggle mobile menu
      menuToggle.once().on('click', function (e) {
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
        mq.matches ? avatarDesktopMenu.append(avatar) : avatarMobileMenu.append(avatar); // eslint-disable-line no-unused-expressions
      }

    }
  };
}(jQuery));
