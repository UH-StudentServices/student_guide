(function ($) {
  'use strict';
  Drupal.behaviors.filterSearch = {
    attach: function (context, settings) {
      var filter = this;

      $('.view-search').once().each(function () {
        var results = $('.view-content article', this);
        var resultCount = $('.view-before-content h2', this);
        var searchFiltersContainer = $('.search-filters-container', this);
        var filterTitles = {
          all: Drupal.t('All', {}, {context: 'Search Filters'}),
          article_general: Drupal.t('General instructions', {}, {context: 'Search Filters'}),
          article_degree_programme_specific: Drupal.t('Degree programme specific instructions', {}, {context: 'Search Filters'}),
          article_other_education_provider_specific: Drupal.t('Other education provider specific instructions', {}, {context: 'Search Filters'}),
          theme: Drupal.t('Theme', {}, {context: 'Search Filters'}),
          news: Drupal.t('Bulletin', {}, {context: 'Search Filters'}),
          degree_students: Drupal.t('Degree Students', {}, {context: 'Search Filters'}),
          doctoral_candidates: Drupal.t('Doctoral Candidates', {}, {context: 'Search Filters'}),
          specialist_training: Drupal.t('Specialist Training', {}, {context: 'Search Filters'}),
          open_university: Drupal.t('Open University', {}, {context: 'Search Filters'}),
        };

        // create buttons for available filter types if more than one type of result
        var availableTypes = filter.getAvailableTypes(results, filter);

        if (availableTypes.length > 1) {
          // add container
          searchFiltersContainer.append('<h3 id="search-filters-label" class="visually-hidden">' + Drupal.t('Refine your search results', {}, {context: 'Search Filters'}) + '</h3>');
          searchFiltersContainer.append('<div id="search-filters" class="button-group is-center-mobile tube"></div>');

          // add 'All' button
          var filterButtons = $('#search-filters', this);
          filterButtons.append(filter.createFilterButton('all', filterTitles, 'is-active'));

          $.each(availableTypes, function (index, type) {
            filterButtons.append(filter.createFilterButton(type, filterTitles));
          });

          // buttons order same as in filterTitles
          filter.reorderButtons(filterButtons, filterTitles);

          // filter results on click
          $('button', filterButtons).on('click', function (event) {
            event.preventDefault();
            filter.filterResults($(this), results, filter);

            // update result count
            var resultCountText = Drupal.t('Results (@results)', {'@results': filter.getResultCount(results)}, {context: 'Search Filters'});
            resultCount.text(resultCountText);
          });
        }
      });
    },

    createFilterButton: function (type, filterTitles, classes) {
      var buttonClasses = classes ? classes + ' button--small' : 'button--small';
      if (filterTitles[type] !== undefined) {
        return '<div class="button-group__button"><button aria-describedby="search-filters-label" aria-pressed="' + (type == 'all' ? 'true' : 'false') + '" class="' + buttonClasses + '" data-type="' + type + '">' + filterTitles[type] + '</button></div>';
      }
    },

    getAvailableTypes: function (results, filter) {
      var availableTypes = [];
      results.each(function () {
        var types = filter.getDataAttributeValues($(this));
        $.each(types, function (index, type) {
          var dupe = availableTypes.find(function (item) {
            return item === type;
          });

          if (!dupe) {
            availableTypes.push(type);
          }
        });
      });
      return availableTypes;
    },

    reorderButtons: function (filterButtons, filterTitles) {
      $('div', filterButtons).sort(function (a, b) {
        var aType = $(a).children('a').attr('data-type');
        var bType = $(b).children('a').attr('data-type');
        var match = null;

        // use order in filterTitles for sorting
        $.each(filterTitles, function (index) {
          if (index === aType) {
            match = -1;
            return false;
          }
          if (index === bType) {
            match = 1;
            return false;
          }
        });
        return match;
      }).appendTo(filterButtons);
    },

    filterResults: function (button, results, filter) {
      var filterType = button.attr('data-type');
      button.addClass('is-active').attr('aria-pressed', 'true');
      button.parent().siblings().children().removeClass('is-active').attr('aria-pressed', 'false');

      results.each(function () {
        var types = filter.getDataAttributeValues($(this));
        if ($.inArray(filterType, types) !== -1 || filterType === 'all') {
          $(this)[0].style.display = 'flex';
        }
        else {
          $(this)[0].style.display = 'none';
        }
      });
    },

    getResultCount: function (results) {
      return results.filter(':visible').length;
    },

    getDataAttributeValues: function (element) {
      var userGroups = element.attr('data-user-group') ? element.attr('data-user-group').split(' ') : [];
      return element.attr('data-type').split(' ').concat(userGroups);
    }
  };
}(jQuery));
