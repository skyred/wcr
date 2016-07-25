(function () {
  "use strict";
  var Block = Backbone.Model.extend({
    defaults:{
      baseUrl: '',
      region: '',
      block: ''
    }
  });

  var BlockList = Backbone.Collection.extend({
    model: Block
  });

  var BlockListView = Backbone.View.extend({
    el: '#blockList',

    template: _.template(" <h2> Blocks </h2>"),

    initialize: function() {
      this.$el.html(this.template(this.model.attributes));
    },

    events: {
      'click .exportButton': 'onExportClicked',
    },
    onExportClicked: function(e) {

    }

  });

  var Router = Backbone.Router.extend({
    routes: {
      "list" : 'viewList',
      "export/:region/:block": "viewDetail"
    },

    viewList: function() {

    },

    viewDetail: function(region, block) {

    }
  })
  var blockView = new BlockListView(
    model: blocklist;
  );
}());
