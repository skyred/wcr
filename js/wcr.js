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

  function importElement(element) {
    var url = getBlockURL(element.name);
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
}());