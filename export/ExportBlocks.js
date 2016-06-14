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
      shadowRoot.innerHTML = r["attachments"]["styles"] +
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