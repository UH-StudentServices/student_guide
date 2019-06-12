(function ($) {
  'use strict';
  Drupal.behaviors.uhsgObar = {
    attach: function (context, settings) {
      function loadScript(scriptName) {
        var scriptElement = document.createElement('script');
        scriptElement.async = false;
        scriptElement.src = settings.uhsgObar.obarBaseUrl + '/' + scriptName + '.js';
        document.body.appendChild(scriptElement);
      };
      
      var isModernBrowser = (
        'fetch' in window
        && 'assign' in Object
      );
      
      if (!isModernBrowser) {
        loadScript('polyfills');
      }
      
      loadScript('obar');
    }
  };
}(jQuery));
