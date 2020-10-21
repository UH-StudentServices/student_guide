(function ($) {
  'use strict';

  // :contains() but case insensitive
  $.expr[':'].containsNoCase = function (a, i, m) {
    return $(a).text().toUpperCase().indexOf(m[3].toUpperCase()) >= 0;
  };

  $.fn.degreeProgrammeFilter = function (options) {
    var opt = $.extend({
      container: '',
      item: '',
      groupingTitle: '',
      ariaLive: '',
      charCount: 2
    }, options);

    return this.each(function () {
      $(this).keyup(function () {
        var filter = $(this).val();

        // by default show all items and grouping titles
        $(opt.item, opt.container).show();
        $(opt.groupingTitle, opt.container).removeClass('visually-hidden');

        // hide items that don't match
        if (filter.length >= opt.charCount) {
          $(opt.item, opt.container).not(':containsNoCase(' + filter + ')').hide();
        }

        // hide grouping title if all children are hidden
        $(opt.groupingTitle, opt.container).each(function () {
          if (!$(this).siblings().find('li:visible').length) {
            $(this).addClass('visually-hidden');
          }
        });

        // Notify assistive technologies of current result count.
        var count =  $(opt.item, opt.container).not(':hidden').length;
        $(opt.ariaLive, opt.container).text(Drupal.t('Showing @count degree programmes.', {'@count': count}));
      });
    });
  };
})(jQuery);
