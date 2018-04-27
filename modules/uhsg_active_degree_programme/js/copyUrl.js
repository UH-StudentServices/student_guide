(function ($) {
  'use strict';
  Drupal.behaviors.copyUrl = {
    attach: function (context, settings) {
      var self = this;
      var copyElement = $('#copy-url');

      copyElement.click(function () {
        var url = $(settings.uhsg_active_degree_programme.selector).attr('href');

        if (url) {
          self.copyToClipboard(url, copyElement);
        }
      });
    },

    copyToClipboard: function (string, copyElement) {
      var textarea = document.createElement('textarea');
      textarea.value = string;
      document.body.appendChild(textarea);
      textarea.select();
      var result = document.execCommand('copy');
      document.body.removeChild(textarea);

      if (result) {
        var originalText = copyElement.val();
        copyElement.val('OK!');
        setTimeout(function () {
          copyElement.val(originalText)
        }, 1000);
      }
    }
  };
}(jQuery));
