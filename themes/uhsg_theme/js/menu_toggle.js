(function ($) {
  'use strict';
  Drupal.behaviors.menu_toggle = {
    attach: function(context, settings) {
      var mainUl = $('.main-menu > ul'),
          menuToggle = $('#menu-toggle');

      menuToggle.once().on('click', function(e) {
        e.preventDefault();
        $(this).toggleClass('active');
        mainUl.toggleClass('is-open').toggleClass('is-slidein');
      });
    }
  };
}(jQuery));
