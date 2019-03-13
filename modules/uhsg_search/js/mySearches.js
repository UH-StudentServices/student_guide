(function ($) {
  'use strict';
  Drupal.behaviors.mySearches = {
    attach: function (context, settings) {

      var cleanupString = this.cleanupString;
      var searchForm = $('.view-search .views-exposed-form');
      var searchInput = $('input[name="search_api_fulltext"]', searchForm);
      var searchString = cleanupString(searchInput.val());
      var mySearches = $.cookie('my_searches') ? JSON.parse($.cookie('my_searches')) : [];
      var empty = $('.view-empty', '.view-search').length;
      var maxLatestSearches = 4;

      // Store submitted value in a cookie.
      if (searchString && !empty) {

        // Avoid duplicates.
        var dupe = mySearches.find(function (item) {
          return item === searchString;
        });

        if (!dupe) {
          mySearches.unshift(searchString);

          if (mySearches.length > maxLatestSearches) {
            mySearches.pop();
          }

          // Store my searches in a cookie.
          $.cookie('my_searches', JSON.stringify(mySearches), {expires: 999});
        }
      }

      // Display searches in a list.
      if (mySearches.length) {
        var content = '';
        mySearches.map(function (value) {
          content += '<li class="list-of-links__link button--action-before theme-transparent">' + cleanupString(value) + '</li>';
        });

        var title = '<span>' + Drupal.t('My searches') + ':</span>';
        var resetButton = '<a class="button--action icon--remove theme-transparent button--reset" title="' + Drupal.t('Remove') + '"></a>';
        $('#my-searches').empty();
        $('#my-searches').append(title + '<ul class="list-of-links__compact">' + content + resetButton + '</ul>');

        // Enable search when clicking one of my searches items.
        $('#my-searches li').on('click', function () {
          searchInput.val(cleanupString($(this).text()));
          searchInput.trigger('keyup');
          searchForm.submit();
        });

        // Empty my searches and delete the cookie.
        $('.button--reset', '#my-searches').on('click', function () {
          $.removeCookie('my_searches');
          $('#my-searches').empty();
        });
      }
    },

    cleanupString: function (text) {
      return text.replace(/[^A-Öa-ö0-9\s!?]/g, '');
    }
  };
}(jQuery));

// Polyfill for Array.prototype.find()
// https://tc39.github.io/ecma262/#sec-array.prototype.find
if (!Array.prototype.find) {
  Object.defineProperty(Array.prototype, 'find', {
    value: function(predicate) {
     // 1. Let O be ? ToObject(this value).
      if (this == null) {
        throw new TypeError('"this" is null or not defined');
      }

      var o = Object(this);

      // 2. Let len be ? ToLength(? Get(O, "length")).
      var len = o.length >>> 0;

      // 3. If IsCallable(predicate) is false, throw a TypeError exception.
      if (typeof predicate !== 'function') {
        throw new TypeError('predicate must be a function');
      }

      // 4. If thisArg was supplied, let T be thisArg; else let T be undefined.
      var thisArg = arguments[1];

      // 5. Let k be 0.
      var k = 0;

      // 6. Repeat, while k < len
      while (k < len) {
        // a. Let Pk be ! ToString(k).
        // b. Let kValue be ? Get(O, Pk).
        // c. Let testResult be ToBoolean(? Call(predicate, T, « kValue, k, O »)).
        // d. If testResult is true, return kValue.
        var kValue = o[k];
        if (predicate.call(thisArg, kValue, k, o)) {
          return kValue;
        }
        // e. Increase k by 1.
        k++;
      }

      // 7. Return undefined.
      return undefined;
    },
    configurable: true,
    writable: true
  });
}