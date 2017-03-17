/* global Drupal, jQuery */

(function ($) {
  'use strict';
  Drupal.behaviors.filterSearch = {
    attach: function() {
      var filter = this;

      $('.view-search').once().each(function() {
        var results = $('.view-content article', this),
            resultCount = $('.view-before-content h3', this),
            filterTitles = {
              'all': Drupal.t('All', {}, {context: 'Search Filters'}),
              'article_general': Drupal.t('General instructions', {}, {context: 'Search Filters'}),
              'article_degree_programme_specific': Drupal.t('Degree programme specific instructions', {}, {context: 'Search Filters'}),
              'theme': Drupal.t('Theme', {}, {context: 'Search Filters'}),
              'news': Drupal.t('News', {}, {context: 'Search Filters'})
            };

        // add container
        resultCount.after('<div id="search-filters" class="button-group is-center-mobile tube"></div>');

        // add 'All' button
        var filterButtons = $('#search-filters', this);
        filterButtons.append(filter.createFilterButton('all', filterTitles, 'is-active'));

        // create buttons for available filter types
        var availableTypes = filter.getAvailableTypes(results);
        $.each(availableTypes, function(index, type) {
          filterButtons.append(filter.createFilterButton(type, filterTitles));
        });

        // buttons order same as in filterTitles
        filter.reorderButtons(filterButtons, filterTitles);

        // filter results on click
        $('a', filterButtons).on('click', function(event) {
          event.preventDefault();
          filter.filterResults($(this), results);

          // update result count
          var resultCountText = Drupal.t('Results (@results)', {'@results': filter.getResultCount(results)}, {context: 'Search Filters'});
          resultCount.text(resultCountText);
        });
      });
    },

    createFilterButton: function(type, filterTitles, classes) {
      var buttonClasses = classes ? classes + ' button--small': 'button--small';
      return '<div class="button-group__button"><a class="' + buttonClasses + '" href="#" data-type="' + type + '">' + filterTitles[type] + '</a></div>';
    },

    getAvailableTypes: function(results) {
      var availableTypes = [];
      results.each(function() {
        var type = $(this).attr('data-type');

        var dupe = availableTypes.find(function(item) {
          return item == type;
        });

        if (!dupe) {
          availableTypes.push(type);
        }
      });
      return availableTypes;
    },

    reorderButtons: function(filterButtons, filterTitles) {
      $('div', filterButtons).sort(function(a, b) {
        var aType = $(a).children('a').attr('data-type'),
            bType = $(b).children('a').attr('data-type'),
            match = null;

        // use order in filterTitles for sorting
        $.each(filterTitles, function(index) {
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
    },

    filterResults: function(button, results) {
      var filterType = button.attr('data-type');
      button.addClass('is-active');
      button.parent().siblings().children().removeClass('is-active');

      results.each(function() {
        if ($(this).attr('data-type') == filterType || filterType == 'all') {
          $(this)[0].style.display = 'flex';
        }
        else {
           $(this)[0].style.display = 'none';
        }
      });
    },

    getResultCount: function(results) {
      return results.filter(':visible').length;
    }
  };
}(jQuery));
