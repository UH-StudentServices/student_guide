(function ($) {
  'use strict';
  Drupal.behaviors.autoselectUserGroupTab = {
    attach: function (context, settings) {
      $(window).on('load', function () {
        var userGroup = settings.uhsg_active_degree_programme.userGroup;

        if (userGroup) {
          var userGroupTabSelector = '#block-themesperusergroup a[href="#' + userGroup + '"]';
          $(userGroupTabSelector).click();
        }
      });
    }
  };
}(jQuery));
