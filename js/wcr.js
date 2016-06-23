(function($) {
  "use strict";
  var baseURL = "";

  var blocks = {};
  var regionList = {};
  var currentPath = '';
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

  function commandUpdate(oldElement, newElement, newPath) {
    removeImport(oldElement);
    var link = importElementFromURL(newElement.region + '/' + newElement.block, newPath);
    var elementNew = document.createElement(newElement['tagname']);
    oldElement.element.parentNode.replaceChild(elementNew, oldElement.element);
    return {
      element: elementNew,
      link: link
    };
  }

  function commandDelete(oldElement) {
    removeImport(oldElement);
    oldElement.element.remove();
  }

  function commandNew(newElement, newPath) {
    var elementNew = document.createElement(newElement['tagname']);
    return {
      link: importElementFromURL(newElement.region + '/' + newElement.block, newPath),
      element: wcr.regions[newElement['region']].element.appendChild(elementNew)
    };
  }

  function loadFromMetadata() {
    var tmp = JSON.parse(drupalSettings.componentsBlockList);
    var regions = tmp['regions'];
    var regionNames = Object.keys(regions);
    wcr.regions = {};
    wcr.blocks[wcr.currentPath] = {};
    for (var i = 0; i < regionNames.length; ++i) {
      var blockNames = Object.keys(regions[regionNames[i]]);
      var regionElement = $("[data-components-display-region='" + regionNames[i] + "']")[0].parentNode;
      wcr.regions[regionNames[i]] = {
        name: regionNames[i],
        element: regionElement,
      };
      for (var j = 0; j < blockNames.length; ++j) {
        var elementId = regionNames[i] + '/' + blockNames[j];
        var link = importElement(elementId);

        wcr.blocks[wcr.currentPath][blockNames[j]] = {
          region: regionNames[i],
          block: blockNames[j],
          tagname: regions[regionNames[i]][blockNames[j]]['element_name'],
          element: $(regions[regionNames[i]][blockNames[j]]['element_name'])[0],
          hash: regions[regionNames[i]][blockNames[j]]['hash'],
          link: link,
        };
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

  function navigateTo(newPath) {

    sendRequest(newPath, function(tmp) {
      var r = tmp['regions'];
      var regionNames = Object.keys(r);
      wcr.blocks[newPath] = [];
      for (var i = 0; i < regionNames.length; ++i) {
        var blockNames = Object.keys(r[regionNames[i]]);
        for (var j = 0; j < blockNames.length; ++j) {
          var elementId = regionNames[i] + '/' + blockNames[j];
          var link = importElementFromURL(elementId, newPath);
          var elementNew = document.createElement(r[regionNames[i]][blockNames[j]]['element_name']);
          wcr.blocks[newPath][blockNames[j]] = {
            region: regionNames[i],
            block: blockNames[j],
            tagname: r[regionNames[i]][blockNames[j]]['element_name'],
            hash: r[regionNames[i]][blockNames[j]]['hash'],
            //link: link,
          };

          if (wcr.blocks[wcr.currentPath][blockNames[j]] == null ){
            //NEW
            var result = commandNew(wcr.blocks[newPath][blockNames[j]], newPath);
            wcr.blocks[newPath][blockNames[j]]['link'] = result['link'];
            wcr.blocks[newPath][blockNames[j]]['element'] = result['element'];
          } else if (wcr.blocks[wcr.currentPath][blockNames[j]]['hash'] != wcr.blocks[newPath][blockNames[j]]['hash']) {
            //UPDATE
            var result = commandUpdate(wcr.blocks[wcr.currentPath][blockNames[j]], wcr.blocks[newPath][blockNames[j]], newPath);
            wcr.blocks[newPath][blockNames[j]]['link'] = result['link'];
            wcr.blocks[newPath][blockNames[j]]['element'] = result['element'];
          } else {
            //SAME
            wcr.blocks[newPath][blockNames[j]]['link'] = wcr.blocks[wcr.currentPath][blockNames[j]]['link'];
            wcr.blocks[newPath][blockNames[j]]['element'] = wcr.blocks[wcr.currentPath][blockNames[j]]['element'];
          }
        }
      }
      wcr.currentPath = newPath;
    });

  }

  function removeAllElements() {
    for (var i = 0; i < wcr.blocks.length; ++i) {
      wcr.blocks[i].element.remove();
    }
    wcr.blocks = {};
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
    currentPath: currentPath,
  };

  if (drupalSettings.componentsBlockList) {
    wcr.currentPath = getCurrentInternalURL();
    wcr.loadFromMetadata();

  }
}(jQuery));