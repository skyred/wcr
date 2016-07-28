(function ($){
  "use strict";

  var shadowRoot;
  var baseURL = "";

  function getBaseURL() {
    var pathArray = location.href.split( '/' );
    var protocol = pathArray[0];
    var host = pathArray[2];
    var url = protocol + '//' + host + '/';
    return url;
  }

  baseURL = getBaseURL();

  function setDrupalURL(drupalURL){
    baseURL = drupalURL;
  }

  function getBlockURL(block) {
    return baseURL;
  }

  function parseStyles(styles) {
    var html = $.parseHTML(styles);
    var i;
    var result = {};
    result['all'] = [];
    result['print'] = [];
    for (i = 0; i < html.length; ++i){
      if (html[i].tagName) {
        if (html[i].tagName == 'LINK') {
          var media = html[i].media;
          var styleSheetUrl = html[i].href;
          if (result[media] == undefined) {
            result[media] = [];
          }
          result[media].push('@import url("' + styleSheetUrl + '");');
        } else if (html[i].tagName == 'STYLE') {
          var media = html[i].media;
          var styleSheetUrls = html[i].textContent;
          if (result[media] == undefined) {
            result[media] = [];
          }
          result[media].push(styleSheetUrls);
        }
      }
    }
    return result;
  }

  function printStyles(styleArray) {
    var i, j;
    var medias = Object.keys(styleArray);
    var result = "";
    for (i = 0; i < medias.length; ++ i) {
      result = result + '<style media="' + medias[i] + '">';
      for (j = 0; j < styleArray[medias[i]].length; ++ j) {
        result = result + styleArray[medias[i]][j];
      }
      result = result + '</style>';
    }
    return result;
  }

  function detectAPI() {
    return document.body.createShadowRoot != null;
  }

  function getBlock(url, block, callback) {
    $.ajax({
      contentType: 'application/json',
      method: 'GET',
      url: baseURL + url,
      data: {
        '_wrapper_format': 'drupal_block',
        'block': block
      }
    }).done(function(result) {
      console.log('success');
      //console.log(result);
      callback(result);
    }).fail(function(e){
      console.log('error');
    });
  }

  function attachShadowDOM(target, url, block) {
    getBlock(url,block, function(r){
      shadowRoot = target.createShadowRoot();
      shadowRoot.innerHTML = printStyles(parseStyles(r["attachments"]["styles"])) +
                             r["attachments"]["scripts"] +
                             r["content"] +
                             r["attachments"]["scripts_bottom"];
      // console.log(r.st)
    });
  }

  if (!detectAPI()) {
    console.log("ShadowDOM is not supported by your browser!");
  }

  window.ExportBlocks = {
    attachShadowDOM: attachShadowDOM,
    getBaseUrl: getBaseURL,
    setDrupalUrl: setDrupalURL
  };
}(jQuery));
