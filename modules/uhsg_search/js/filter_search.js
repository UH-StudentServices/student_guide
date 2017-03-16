(function ($) {
  'use strict';
  Drupal.behaviors.uhsg_search_filter_search = {
    attach: function(context, settings) {
      var view = '.view-search',
          results = $('.view-content article', view),
          numResults = $('.view-before-content h3', view).after('<div id="search-filters" class="button-group is-center-mobile"></div>'),
          filterTitles = {
            'article_degree_programme_specific': Drupal.t('Degree programme specific instructions', {}, {context: 'Search Filters'}),
            'article_general': Drupal.t('General instructions', {}, {context: 'Search Filters'}),
            'theme': Drupal.t('Theme', {}, {context: 'Search Filters'}),
            'news': Drupal.t('News', {}, {context: 'Search Filters'}),
            'all': Drupal.t('All', {}, {context: 'Search Filters'})
          },
          filterButtons = $('#search-filters', view).append(createFilterButton('all')),
          filterOptions = [];
      
      results.each(function() {
        var type = $(this).attr('data-type');

        var dupe = filterOptions.find(function(item) {
          return item == type;
        });

        if (!dupe) {
          filterOptions.push(type);
          filterButtons.append(createFilterButton(type));
        }
      });

      $('a', filterButtons).on('click', function(e) {
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
        var numResultsText = Drupal.t('Results (@results)', {'@results': getNumResults()}, {context: 'Search Filters'});
        numResults.text(numResultsText);
      });

      function createFilterButton(type) {
        return '<div class="button-group__button"><a class="button--small" href="#" data-type="' + type + '">' + filterTitles[type] + '</a></div>';
      }

      function getNumResults() {
        return results.filter(':visible').length;
      }

    }
  };
}(jQuery));
