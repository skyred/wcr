(function() {
  "use strict";
  var baseURL = "";

  var blocks = [];
  function getBaseURL() {
    var pathArray = location.href.split( '/' );
    var protocol = pathArray[0];
    var host = pathArray[2];
    var url = protocol + '//' + host + '/';
    return url;
  }

  function setDrupalURL(drupalURL){
    baseURL = drupalURL;
  }

  function getBlockURL(block, internalURL) {
    return baseURL + internalURL + "?_wrapper_format=drupal_block&block=" + block + '&mode=bare';
  }

  function getCurrentInternalURL() {
    return location.pathname;
  }

  function importElement(elementName) {
    var url = getBlockURL(elementName, getCurrentInternalURL());
    var link = document.createElement('link');
    link.rel = 'import';
    link.href = url;
    document.head.appendChild(link);
  }

  function removeImport(element) {
    var oldLink = element.importLink;
    document.head.removeChild(oldLink);
  }

  function commandUpdate(oldElement, newElement) {
    removeImport(blocks[oldElement]);
    blocks.push(newElement);
    importElement(newElement);
  }

  function commandDelete(oldElement) {
    removeImport(blocks[oldElement]);
  }

  function commandNew(newElement) {
    blocks.push(newElement);
    importElement(newElement);
  }

  function loadFromMetadata () {
    var regions = JSON.parse(drupalSettings.componentsBlockList);
    var regionNames = Object.keys(regions);
    for (var i = 0; i < regionNames.length; ++i) {
      var blockNames = Object.keys(regions[regionNames[i]]);
      for (var j = 0; j < blockNames.length; ++j) {
        var elementId = regionNames[i] + '/' + blockNames[j];
        importElement(elementId);
      }
    }
  }

  window.wcr = {
    getCurrentInternalURL : getCurrentInternalURL,
    importElement: importElement,
    loadFromMetadata: loadFromMetadata,

  };
}());