(function ($, Backbone) {
  "use strict";
  window.Drupal = window.Drupal || {};
  Drupal.wcr = Drupal.wcr || {};
  var wcr = Drupal.wcr;

  $.fn._centerMe = function () {
    this.css("position","fixed");
    this.css("top", ( $(window).height() - this.height() ) / 2+$(window).scrollTop() + "px");
    this.css("left", ( $(window).width() - this.width() ) / 2+$(window).scrollLeft() + "px");
    return this;
  };

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

    getExportUrl: function(block, mode) {
      var tmpUrl = new drupalUrlHelper.Url(window.location.href);
      tmpUrl.params['_wrapper_format'] = 'drupal_wcr';
      tmpUrl.params['_wcr_block'] = block;
      tmpUrl.params['_wcr_mode'] = mode;
      return tmpUrl.toString();
    },

    getCode: function () {
      if (this.get('mode') == "iframe") {
        return '<iframe style="border:0; width:100%;" src="'+this.getExportUrl(this.get('blockName'), 'singleblock')+'">';
      }
      return 'unknown';
    }

  });

  wcr.DialogView = Backbone.View.extend({
    el: '#wcrExportDialog',

    events: {
      "click #close": "dialogClose",
      "change #mode": "modeChange"
    },

    initialize: function () {
      this.$el.addClass("ui-dialog");
      this.$el.addClass( 'hidden');

      var content = '<div class="ui-dialog-titlebar ui-corner-all ui-widget-header">'
                     +  '<span class="ui-dialog-title">Block Export Helper</span>'
                     +  '<button id="close" class="ui-button ui-widget ui-corner-all ui-button-icon-only ui-dialog-titlebar-close">'
                     +     '<span class="ui-button-icon-primary ui-icon ui-icon-closethick"></span><span class="ui-button-text">Close</span>'
                     +  '</button>'
                     +'</div>'
                     + '<div class="ui-front ui-dialog-content ui-widget-content">'
                     +    '<div class="ui-options clearfix form--inline">'
                     +      '<div class="js-form-item form-item js-form-type-textfield form-type-textfield">'
                     +        '<label>Block name</label> <span id="block-name"></span>'
                     +      '</div>'
                     +      '<div class="js-form-item form-item js-form-type-select form-type-select">'
                     +        '<label>Embedding Mode</label> <select class="form-select" id="mode"></select>'
                     +      '</div>'
                     +    '</div>'
                     + '<div>'
                     +   '<label>Code</label> <textarea id="code"></textarea>'
                     + '</div>'
                     + '<div><label>Preview</label> <iframe id="preview"></iframe></div>'
                     + '</div>';

      this.$el.html(content);

      this.$el._centerMe();
      $(window).resize(function() {this.$el._centerMe()}.bind(this));

      this.txtBlockName = this.$('#block-name');
      this.drpdwnMode = this.$('#mode');
      this.txtCode = this.$('#code');
      this.ifrmPreview = this.$('iframe');
      this.btnClose = this.$('#close');

      this.drpdwnMode.html('<option value="iframe">iFrame</option>' +
                           '<option value="webcomponent">HTML Import</option>' +
                           '<option value="shadowdom">JS + ShadowDOM</option>');

      this.listenTo(this.model, 'show', this.showDialog);
      this.listenTo(this.model, 'hide', this.hideDialog);
      this.model.on('change', this.render, this);
    },

    render: function () {
      this.txtBlockName.text(this.model.get('blockName'));
      this.txtCode.val(this.model.getCode());
      this.ifrmPreview.contents().find('html').html(this.model.getCode());
     // this.drpdwnMode.selected = wcr.DialogModel.blockName;

      if (this.model.get('visibility') == false) {
        this.$el.addClass('hidden');
      } else {
        this.$el.removeClass('hidden');
      }
      this.$el._centerMe();

    },

    showDialog: function () {
      this.model.showDialog();
    },

    dialogClose: function() {
      this.model.hideDialog();
    },

    modeChange: function() {
      this.model.save('mode', this.drpdwnMode.val());
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


