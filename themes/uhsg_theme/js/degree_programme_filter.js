(function ($) {
  'use strict';

  // :contains() but case insensitive
  $.expr[':'].containsNoCase = function(a, i, m) {
    return $(a).text().toUpperCase().indexOf(m[3].toUpperCase()) >= 0;
  };

  $.fn.degreeProgrammeFilter = function(options) {
    var opt = $.extend({
      container: '',
      item: '',
      groupingTitle: '',
      charCount: 2
    }, options);

    return this.each(function() {
      $(this).keyup(function() {
        var filter = $(this).val();

        // by default show all items and grouping titles
        $(opt.item).show();
        $(opt.groupingTitle).show();

        // hide items that don't match
        if (filter.length >= opt.charCount) {
          $(opt.item).not(":containsNoCase(" + filter + ")").hide();
        }

        // hide grouping title if all children are hidden
        $(opt.groupingTitle).each(function() {
          if (!$(this).next('ul').children('li:visible').length) {
            $(this).hide();
          }
        });

      });
    });
  };
})(jQuery);
