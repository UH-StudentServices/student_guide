(function ($) {
  'use strict';
  Drupal.behaviors.theme_sort = {
    attach: function(context, settings) {
      if (!Modernizr.touchevents) {
        $('.view-themes .view-content').once().each(function() {
          Sortable.create(this, {
            animation: 250,
            group: 'theme-sort',
            store: {
              get: function (sortable) {
                var order = localStorage.getItem(sortable.options.group.name);
                return order ? order.split('|') : [];
              },
              set: function (sortable) {
                var order = sortable.toArray();
                localStorage.setItem(sortable.options.group.name, order.join('|'));
              }
            }
          });
        });
      }
    }
  };
}(jQuery));
