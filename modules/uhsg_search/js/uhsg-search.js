(function ($) {
  'use strict';
  Drupal.behaviors.uhsg_search_reset = {
    attach: function(context, settings) {

      // Exposed filter reset redirects user to 404 page on AJAX view when
      // placed as a block (https://www.drupal.org/node/2820347). Until core is
      // fixed, using location.reload() to reload the current page to reset the
      // form.
      $('#views-exposed-form-search-block-1 input.button--reset').click(function (e) {
        e.preventDefault();
        location.reload();
      });

      var app = {};
      var searchInput = $('#views-exposed-form-search-block-1 input[name="search_api_fulltext"]');
      var searchSubmit = $('#views-exposed-form-search-block-1 .form-submit');

      // Model
      app.MySearchList = Backbone.Model.extend({
        defaults: {
          id: '',
        }
      });

      // Collection
      app.MySearches = Backbone.Collection.extend({
        model: app.MySearchList,
        localStorage: new Store("my-searches")
      });

      // instance of the Collection
      app.MySearches= new app.MySearches();

      // view that renders individual search item (li)
      app.SearchItem = Backbone.View.extend({
        tagName: 'li',
        className: 'list-of-links__link button--action-before icon--search theme-transparent',
        render: function(){
          this.$el.html(this.model.get('id'));
          return this; // enable chained calls
        },
        events: {
          'click': 'enableSearch',
        },
        enableSearch: function() {
          searchInput.val(this.model.get('id'));
          searchSubmit.click();
        }
      });

      // view that renders the full list of search items calling SearchItem for each one.
      app.AppView = Backbone.View.extend({
        el: '#my-searches',
        initialize: function () {
          app.MySearches.on('add', this.addAll, this);
          app.MySearches.fetch(); // Loads list from local storage
          searchSubmit.on('click', this.handleOnSubmit);
        },
        events: {
          'click .clear': 'clear'
        },
        handleOnSubmit: function(e){
          app.MySearches.create({
            id: searchInput.val(),
          });
        },
        addOne: function(item){
          var view = new app.SearchItem({model: item});
          $('#my-searches-list').append(view.render().el);
        },
        addAll: function(){
          this.$('#my-searches-list').html(''); // clean list
          app.MySearches.each(this.addOne, this);
        },
        clear: function(){
          _.invoke(app.MySearches.toArray(), 'destroy');
          this.$('#my-searches-list').html('');
        },
      });

      // Initialize
      app.appView = new app.AppView();
    }
  };
}(jQuery));
