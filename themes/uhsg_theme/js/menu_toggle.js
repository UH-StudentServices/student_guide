(function ($) {
  'use strict';

  Drupal.behaviors.menu_toggle = {
    attach: function(context, settings) {
      $('.toggle', context).once().on('click', function() {
      	var toggleText = $(this).hasClass('collapsed') ? Drupal.t('Close') : Drupal.t('Change');
      	$(this).toggleClass('collapsed')
      		.text(toggleText)
      		.parent()
      		.next('.toggle-target')
      		.toggleClass('visually-hidden');
      });
    }
  };

}(jQuery));
