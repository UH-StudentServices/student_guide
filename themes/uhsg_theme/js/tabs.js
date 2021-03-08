(function ($) {
  'use strict';

  Drupal.behaviors.uhsgTabs = {
    attach: function (context, settings) {
      // Check if we have a hash in the url. No need to continue, if not.
      var hash = window.location.hash;
      if (!hash) {
        return;
      }

      // Find all tab containers. No need to continue, if none found.
      var tabContainers = $('.js-tabs', context);
      if (!tabContainers.length) {
        return;
      }

      // Observer options.
      var observerConfig = { attributes: true, childList: false, subtree: false };

      // Mutation observer callback.
      var observerCallback = function(mutationsList, observer) {
        for (var i = 0, len = mutationsList.length; i < len; i++) {
          var mutation = mutationsList[i];

          if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
            if ($(mutation.target).hasClass('is-initialized')) {
              activateTab(hash);
              scrollToTab(hash);

              // The deed is done. No need to continue this loop or even
              // observe further.
              observer.disconnect();
              break;
            }
          }
        }
      };

      // Create an observer instance linked to the callback function.
      const observer = new MutationObserver(observerCallback);

      // Start observing tab containers for configured mutations.
      tabContainers.each(function() {
        var tabContainer = $(this);

        // No need to observe, if given hash id isn't inside this container.
        if (tabContainer.find(hash).length === 0) {
          return;
        }

        if (tabContainer.hasClass('is-initialized')) {
          // Already initialized, no need to observe. Just activate the tab
          // and scroll the window to it.
          activateTab(hash);
          scrollToTab(hash);
        }
        else {
          // Start observing.
          observer.observe(this, observerConfig);
        }
      });

      // Activate given tab.
      var activateTab = function(tabHash) {
        var clickEvent = new Event('click');
        $('a[href="' + hash + '"]').get(0).dispatchEvent(clickEvent);
      };

      // Scroll window to given tab.
      var scrollToTab = function(tabHash) {
        $(hash).get(0).scrollIntoView();
      };
    }
  };

}(jQuery));
