(function ($) {
  'use strict';

  Drupal.behaviors.menu_toggle = {
    attach: function(context, settings) {
      $('.degree-programme-switcher__toggle', '.degree-programme-switcher').once().on('click', function() {
      	var toggleText = $(this).hasClass('collapsed') ? Drupal.t('Change') : Drupal.t('Close');
      	$(this).toggleClass('collapsed')
      		.text(toggleText)
      		.next('.degree-programme-switcher__container')
      		.toggleClass('visually-hidden');
      });
    }
  };

}(jQuery));
