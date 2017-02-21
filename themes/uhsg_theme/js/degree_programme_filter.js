(function($, undefined) {
  $.expr[":"].containsNoCase = function(el, i, m) {
    var search = m[3];
    if (!search) return false;
    return new RegExp(search, "i").test($(el).text());
  };

  $.fn.searchFilter = function(options) {
    var opt = $.extend({
      targetSelector: '',
      charCount: 1
    }, options);

    return this.each(function() {
      $(this).keyup(function() {
        var search = $(this).val();
        $(opt.targetSelector).show();

        if (search && search.length >= opt.charCount) {
          $(opt.targetSelector).not(":containsNoCase(" + search + ")").hide();
        }
      });
    });
  };
})(jQuery);
