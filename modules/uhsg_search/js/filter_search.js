(function ($) {
  'use strict';
  Drupal.behaviors.uhsg_search_filter_search = {
    attach: function(context, settings) {
      var titles = {
            'article_degree_programme_specific': Drupal.t('Degree programme specific instructions'),
            'article_general': Drupal.t('General instructions'),
            'theme': Drupal.t('Theme'),
            'news': Drupal.t('News'),
            'all': Drupal.t('All')
          },
          view = '.view-search',
          results = $('.view-content article', view),
          numResults = $('.view-result h3', view).after('<div id="search-filters" class="button-group"></div>'),
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
        numResults.text(Drupal.t('Results' + results.filter(':visible').length));
      });

      function createFilterButton(type) {
        return '<div class="button-group__button"><a class="button--small" href="#" data-type="' + type + '">' + titles[type] + '</a></div>';
      }

    }
  };
}(jQuery));
