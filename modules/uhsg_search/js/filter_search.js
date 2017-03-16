(function ($) {
  'use strict';
  Drupal.behaviors.uhsg_search_filter_search = {
    attach: function(context, settings) {
      var view = '.view-search',
          results = $('.view-content article', view),
          numResults = $('.view-before-content h3', view).after('<div id="search-filters" class="button-group is-center-mobile"></div>'),
          filterTitles = {
            'all': Drupal.t('All', {}, {context: 'Search Filters'}),
            'article_general': Drupal.t('General instructions', {}, {context: 'Search Filters'}),
            'article_degree_programme_specific': Drupal.t('Degree programme specific instructions', {}, {context: 'Search Filters'}),
            'theme': Drupal.t('Theme', {}, {context: 'Search Filters'}),
            'news': Drupal.t('News', {}, {context: 'Search Filters'})
          },
          filterButtons = $('#search-filters', view).append(createFilterButton('all', 'is-active')),
          filterOptions = [];
      
      // create buttons for available filter types
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

      // reorder buttons
      $('div', filterButtons).sort(function(a, b) {
        var aType = $(a).children('a').attr('data-type'),
            bType = $(b).children('a').attr('data-type'),
            match = null;

        // use order in filterTitles for sorting
        $.each(filterTitles, function(index, value) {
          if (index == aType) {
            match = -1;
            return false;
          }
          if (index == bType) {
            match = 1;
            return false;
          }
        });
        return match;
      }).appendTo(filterButtons);

      $('a', filterButtons).on('click', function(e) {
        e.preventDefault();
        var filterType = $(this).attr('data-type');
        $(this).addClass('is-active');
        $(this).parent().siblings().children().removeClass('is-active');
        results.each(function() {
          if ($(this).attr('data-type') == filterType || filterType == 'all') {
            $(this)[0].style.display = 'flex';
          }
          else {
             $(this)[0].style.display = 'none';
          }
        });
        var numResultsText = Drupal.t('Results (@results)', {'@results': getNumResults()}, {context: 'Search Filters'});
        numResults.text(numResultsText);
      });

      function createFilterButton(type, classes) {
        var buttonClasses = classes ? classes + ' button--small': 'button--small';
        return '<div class="button-group__button"><a class="' + buttonClasses + '" href="#" data-type="' + type + '">' + filterTitles[type] + '</a></div>';
      }

      function getNumResults() {
        return results.filter(':visible').length;
      }

    }
  };
}(jQuery));
