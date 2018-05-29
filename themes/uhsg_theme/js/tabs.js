(function ($) {
  'use strict';
  Drupal.behaviors.tabs = {
    attach: function (context, settings) {
      $('#tabs').once('tabs').tabs({
        classes: {
          'ui-tabs-active': 'selected'
        }
      });
    }
  };
}(jQuery));
