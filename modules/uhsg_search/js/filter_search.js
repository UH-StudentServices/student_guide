(function ($) {
  'use strict';
  Drupal.behaviors.uhsg_search_filter_search = {
    attach: function(context, settings) {
      var view = '.view-search',
          results = $('.view-content article', view),
          numResults = $('.view-result', view),
          filterOptions = [];

      numResults.after('<div id="search-filters" class="button-group"><div class="button-group__button"><a class="button--small" href="#" data-type="all">' + Drupal.t('All') + '</a></div></div>');
      
      results.each(function() {
        var type = $(this).attr('data-type');

        var dupe = filterOptions.find(function(item) {
          return item == type;
        });

        if (!dupe) {
          filterOptions.push(type);
          $('#search-filters', view).append('<div class="button-group__button"><a class="button--small" href="#" data-type="' + type + '">' + Drupal.t(type) + '</a></div>');
        }
      });

      $('a', '#search-filters').on('click', function(e) {
        e.preventDefault();
        var filterType = $(this).attr('data-type');
        $(this).addClass('is-active');
        $(this).parent().siblings().children().removeClass('is-active');
        results.each(function() {
          if ($(this).attr('data-type') == filterType || filterType == 'all') {
            $(this).show();
          }
          else {
            $(this).hide();
          }
        });
      });

    }
  };
}(jQuery));

