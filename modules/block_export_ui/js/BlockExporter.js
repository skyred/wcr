(function ($, Backbone) {
  "use strict";
  window.Drupal = window.Drupal || {};
  Drupal.wcr = Drupal.wcr || {};
  var wcr = Drupal.wcr;

  wcr.DialogModel = Backbone.Model.extend({
    defaults:{
      currentUrl: '', //URL without wcr parameters
      region: '',
      blockName: '',
      mode: 'iframe', // Mode of embedding: 'iframe', 'webcomponents', 'shadowdom'.
      visibility: false,
    },

    setMode: function(mode) {
      this.set('mode', mode);
    },

    showDialog: function() {
      this.set('visibility', true);
    },

    hideDialog: function() {
      this.set('visibility', false);
    },

    setBlockName: function(name) {
      this.set('blockName', name);
    },

    doExportBlock: function(blockName) {
      this.setBlockName(blockName);
      this.showDialog();
    },

    getCode: function () {
      if (this.get('mode')=="iframe") {
        return '<iframe src="http://localhost/?_wcr_block='+this.get('blockName')+'">';
      }
      return 'unknown';
    }

  });

  wcr.DialogView = Backbone.View.extend({
    el: '#wcrExportDialog',

    events: {
      "click #close": "dialogClose"
    },

    initialize: function () {
      this.$el.addClass("ui-dialog");
      this.$el.addClass( 'hidden');

      var content = '<div class="ui-dialog-titlebar ui-corner-all ui-widget-header">'
                     +  '<span class="ui-dialog-title">Export</span>'
                     +  '<button id="close" class="ui-button ui-widget ui-corner-all ui-button-icon-only ui-dialog-titlebar-close">'
                     +     '<span class="ui-button-icon-primary ui-icon ui-icon-closethick"></span><span class="ui-button-text">Close</span>'
                     +  '</button>'
                     +'</div>'
                     + '<div class="ui-front ui-dialog-content ui-widget-content">'
                     + 'Block name: <span id="block-name"></span>'
                     + 'code: <textarea id="code"></textarea>'
                     + '</div>';

      this.$el.html(content);

      this.txtBlockName = this.$('#block-name');
      this.drpdwnMode = this.$('#mode');
      this.txtCode = this.$('#code');
      this.ifrmPreview = this.$('iframe')[0];
      this.btnClose = this.$('#close');

      this.listenTo(this.model, 'show', this.showDialog);
      this.listenTo(this.model, 'hide', this.hideDialog);
      this.model.on('change', this.render, this);
    },

    render: function () {
      this.txtBlockName.text(this.model.get('blockName'));
      this.txtCode.val(this.model.getCode());
     // this.drpdwnMode.selected = wcr.DialogModel.blockName;

      if (this.model.get('visibility') == false) {
        this.$el.addClass('hidden');
      } else {
        this.$el.removeClass('hidden');
      }


    },

    showDialog: function () {
      this.model.showDialog();
    },

    dialogClose: function() {
      this.model.hideDialog();
    },

    doExportBlock: function(blockName) {
      this.model.doExportBlock(blockName);

    }

  });

  $(function (){
    wcr.dialog = new wcr.DialogView({model: new wcr.DialogModel()});

    $('body').on('click', 'a', function (event) {
      // Middle click, cmd click, and ctrl click should open links as normal.
      if (event.which > 1 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
        return;
      }

      var target = event.currentTarget;
      var href = $(target).attr('href');
      if (href.startsWith('/block-export/pseudo')) {
        event.preventDefault();
        var block = $.deparam(href.slice(26)).block;
        Drupal.wcr.dialog.doExportBlock(block);
        console.log('hi', target);
      }
    });
  });

}(jQuery, Backbone));


