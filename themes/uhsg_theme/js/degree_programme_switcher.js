(function ($) {
  'use strict';
  Drupal.behaviors.degreeProgrammeSwitcher = {
    attach: function(context, settings) {
      var degreeProgrammeSwitcher = '.degree-programme-switcher',
          container = $('.degree-programme-switcher'),
          header = $('.degree-programme-switcher__header', degreeProgrammeSwitcher),
          toggle = $('.degree-programme-switcher__toggle', degreeProgrammeSwitcher),
          searchInput = $('input[name="name"]', degreeProgrammeSwitcher),
          toggleClass = 'collapsed',
          toggleTextClosed = Drupal.t('Change'),
          toggleTextOpen = Drupal.t('Close'),
          breakpoints = settings.breakpoints;

      // toggle collapsed when clicking header
      header.once().on('click', function() {
        container.toggleClass(toggleClass);
        $('body').toggleClass('no-scroll-mobile');
        toggle.text(container.hasClass(toggleClass) ? toggleTextOpen : toggleTextClosed);

        if (window.matchMedia(breakpoints['small']).matches) {
          searchInput.focus();
        }
      });

      // close when clicking outside
      $(document).once().on('click', function(e) {
        var clickedOutside = $(e.target).parents(degreeProgrammeSwitcher).length == 0;
        if (container.hasClass(toggleClass) && clickedOutside) {
          container.removeClass(toggleClass);
          $('body').removeClass('no-scroll-mobile');
          toggle.text(container.hasClass(toggleClass) ? toggleTextOpen : toggleTextClosed);
        }
      });

      var viewName = 'degree_programmes';
      var instances = Drupal.views.instances;
      var view;

      // find correct dom id
      $.each(instances , function( index, element) {
        if (element.settings.view_name == viewName ) {
          view = $('.view-dom-id-' + element.settings.view_dom_id);
        }
      });

      $('.flag-my_degree_programmes').click(function() {
        container.find('.form-submit').click();
        // TODO: make this work
        //view.triggerHandler('RefreshView');
      });



    }
  };
}(jQuery));
