(function ($) {
  'use strict';
  // Truncate text in browser, so that search engines can pick up whole texts.
  Drupal.behaviors.truncateText = {
    attach: function (context, settings) {
      var maxLength = settings.truncateText.maxLength;
      var elements = $(settings.truncateText.selector);
      var ellipsis = '...';

      elements.each(function () {
        var element = $(this);

        if (element.text().length > maxLength) {
          element.text(element.text().substr(0, maxLength).concat(ellipsis));
        }
      })
    }
  };
}(jQuery));
