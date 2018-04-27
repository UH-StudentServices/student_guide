(function ($) {
  'use strict';
  Drupal.behaviors.copyUrl = {
    attach: function (context, settings) {
      var self = this;

      $('#copy-url').click(function () {
        var url = $(settings.uhsg_active_degree_programme.selector).attr('href');

        if (url) {
          self.copyToClipboard(url);
        }
      });
    },

    copyToClipboard: function (string) {
      var textarea = document.createElement('textarea');
      textarea.value = string;
      document.body.appendChild(textarea);
      textarea.select();
      document.execCommand('copy');
      document.body.removeChild(textarea);
    }
  };
}(jQuery));
