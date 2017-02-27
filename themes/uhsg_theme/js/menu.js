(function ($) {
  'use strict';
  Drupal.behaviors.menu = {
    attach: function(context, settings) {
      var mainUl = $('.main-menu > ul'),
          navToggle = $('#menu-toggle');

      navToggle.once().on('click', function(e) {
        e.preventDefault();
        $(this).toggleClass('active');
        mainUl.toggleClass('is-open').toggleClass('is-slidein');
      });
    }
  };
}(jQuery));
