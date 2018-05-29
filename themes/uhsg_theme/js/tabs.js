(function ($) {
  'use strict';
  Drupal.behaviors.tabs = {
    attach: function (context, settings) {
      $('#tabs').once('tabs').tabs();
    }
  };
}(jQuery));
