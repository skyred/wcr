(function($) {
  "use strict";
  var baseURL = "";

  var blocks = [];
  var regionList = [];
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
    return importElementFromURL(elementName, getCurrentInternalURL());
  }

  function importElementFromURL(elementName, internalURL) {
    var url = getBlockURL(elementName, internalURL);
    var link = document.createElement('link');
    link.rel = 'import';
    link.href = url;
    return document.head.appendChild(link);
  }

  function removeImport(element) {
    var oldLink = element.link;
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
    var tmp = JSON.parse(drupalSettings.componentsBlockList);
    var regions = tmp['regions'];
    var regionNames = Object.keys(regions);
    for (var i = 0; i < regionNames.length; ++i) {
      var blockNames = Object.keys(regions[regionNames[i]]);
      var regionElement = $("[data-components-display-region='" + regionNames[i] + "']")[0].parentNode;
      regionList.push({
        name: regionNames[i],
        element: regionElement,
      });
      for (var j = 0; j < blockNames.length; ++j) {
        var elementId = regionNames[i] + '/' + blockNames[j];
        var link = importElement(elementId);

        this.blocks.push({
          region: regionNames[i],
          block: blockNames[j],
          element: $(regions[regionNames[i]][blockNames[j]]['element_name']),
          link: link,
        })
      }
    }
  }

  function convertToElementName(str) {
    var tmp = str.replace(/_/g, '-');
    if (tmp.indexOf('-') == -1) {
      tmp = 'x-' + tmp;
    }
    return tmp;
  }

  function removeAllImports() {
    for (var i = 0; i < wcr.blocks.length; ++i) {
      removeImport(wcr.blocks[i]);
    }
  }

  function sendRequest(internalURL, callback) {
    $.ajax({
      method: 'GET',
      url: baseURL + internalURL,
      data: {
        '_wrapper_format': 'drupal_components',
      }
    }).done(function(result) {
      console.log('success');
      //console.log(result);
      callback(result);
    }).fail(function(e){
      console.log('error');
    });
  }

  function findRegion(regionName) {
    for (var i = 0; i < wcr.regions.length; ++i) {
      if (wcr.regions[i].name == regionName)
        return wcr.regions[i];
    }
    return null;
  }

  function navigateTo(internalURL) {
    this.removeAllImports();
    this.removeAllElements();
    sendRequest(internalURL, function(tmp) {
      var r = tmp['regions'];
      var hashSuffix = tmp['hash'];
      var regionNames = Object.keys(r);
      for (var i = 0; i < regionNames.length; ++i) {
        var blockNames = Object.keys(r[regionNames[i]]);
        for (var j = 0; j < blockNames.length; ++j) {
          var elementId = regionNames[i] + '/' + blockNames[j];
          var link = importElementFromURL(elementId, internalURL);
          var elementNew = document.createElement(convertToElementName(blockNames[j]) + '-' + hashSuffix);
          wcr.blocks.push({
            region: regionNames[i],
            block: blockNames[j],
            element: findRegion(regionNames[i]).element.appendChild(elementNew),
            link: link,
          })
        }
      }
    });
  }

  function removeAllElements() {
    for (var i = 0; i < wcr.blocks.length; ++i) {
      wcr.blocks[i].element.remove();
    }
    wcr.blocks = [];
  }

  window.wcr = {
    getCurrentInternalURL : getCurrentInternalURL,
    importElement: importElement,
    loadFromMetadata: loadFromMetadata,
    blocks: blocks,
    regions: regionList,
    removeAllImports: removeAllImports,
    removeAllElements: removeAllElements,
    convertToElementName: convertToElementName,
    navigateTo: navigateTo,
  };

  if (drupalSettings.componentsBlockList) {
    wcr.loadFromMetadata();
  }
}(jQuery));