(function($, undefined) {
  $.expr[":"].containsNoCase = function(el, i, m) {
    var search = m[3];
    if (!search) return false;
    return new RegExp(search, "i").test($(el).text());
  };

  $.fn.searchFilter = function(options) {
    var opt = $.extend({
      containerSelector: '',
      itemSelector: '',
      groupingTitleSelector: '',
      charCount: 1
    }, options);

    return this.each(function() {
      $(this).keyup(function() {
        var search = $(this).val();
        $(opt.itemSelector).show();
        $(opt.groupingTitleSelector).show();

        if (search && search.length >= opt.charCount) {
          $(opt.itemSelector).not(":containsNoCase(" + search + ")").hide();
        }

        // hide grouping title if all children are hidden
        $(opt.groupingTitleSelector).each(function() {
          if (!$(this).next('ul').children('li:visible').length) {
            $(this).hide();
          }
        });

      });
    });
  };
})(jQuery);
