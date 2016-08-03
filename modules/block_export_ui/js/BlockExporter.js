(function ($, Backbone) {
  "use strict";
  var Drupal = Drupal || {};
  Drupal.wcr = Drupal.wcr || {};
  var wcr = Drupal.wcr;

  wcr.DialogModel = Backbone.Model.extend({
    defaults:{
      currentUrl: '', //URL without wcr parameters
      region: '',
      block: '',
      mode: '', // Mode of embedding: 'iframe', 'webcomponents', 'shadowdom'.

    }
  });

  wcr.DialogView = Backbone.View.extend({
    el: '#wcrExportDialog',

    initialize: function () {
      this.$.addClass("ui-dialog");
      this.innerHTML = '<div class="ui-dialog-titlebar">Export</div>'
      this.txtBlockName = this.$('#block-name');
      this.drpdwnMode = this.$('#mode');
      this.txtCode = this.$('#code');
      this.ifrmPreview = this.$('iframe')[0];

      this.listenTo(wcr.DialogModel, 'show', showDialog);
      this.listenTo(wcr.DialogModel, 'hide', hideDialog);
      this.listenTo(wcr.DialogModel, 'mode:change', render);
    },

    render: function () {
      this.txtBlockName.textContent = wcr.DialogModel.blockName;
      this.drpdwnMode.selected = wcr.DialogModel.blockName;

    },

    showDialog: function () {
      this.$.show();
    },

    hideDialog: function () {
      this.$.hide();
    },


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

  $(function (){
    new wcr.DialogView();
  })
}(jQuery, Backbone));


