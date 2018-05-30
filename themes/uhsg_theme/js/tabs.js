(function ($) {
  'use strict';

  // Initializes jQuery UI Tabs.
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
