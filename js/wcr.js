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
    var url = protocol + '//' + host;
    return url;
  }

  function setDrupalURL(drupalURL){
    baseURL = drupalURL;
  }

  function getBlockURL(block, internalURLObject) {
    var tmp = $.extend(true, {} ,internalURLObject); //TODO: remove reliance on jQuery
    tmp.params['_wrapper_format'] = 'drupal_block';
    tmp.params['block'] = block;
    tmp.params['mode'] = 'bare';

    return baseURL + tmp.internalPath();
  }


  function importElement(elementName) {
    return importElementFromURL(elementName, getCurrentInternalURL());
  }

  function importElementFromURL(elementName, internalURLObject) {
    var url = getBlockURL(elementName, internalURLObject);
    var link = document.createElement('link');
    link.rel = 'import';
    link.href = url;
    return document.head.appendChild(link);
  }

  function removeImport(element) {
    var oldLink = element.link;
    document.head.removeChild(oldLink);
  }

  function commandUpdate(oldElement, newElement, newPathObject) {
    removeImport(oldElement);
    var link = importElementFromURL(newElement.region + '/' + newElement.block, newPathObject);
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

  function commandNew(newElement, previousElement, newPathObject) {
    var elementNew = document.createElement(newElement['tagname']);
    if (previousElement == null) {
      var previousNode = wcr.regions[newElement['region']].element.firstChild;
    } else {
      var previousNode = previousElement.element;
    }
    return {
      link: importElementFromURL(newElement.region + '/' + newElement.block, newPathObject),
      element: wcr.regions[newElement['region']].element.insertBefore(elementNew, previousNode.nextSibling),
      // Insert before next sibling => insert after
    };
  }

  function loadFromMetadata() {
    var tmp = JSON.parse(drupalSettings.componentsBlockList);
    var regions = tmp['regions'];
    var regionNames = Object.keys(regions);
    wcr.regions = {};
    wcr.blocks[wcr.currentPath.internalPath()] = {};
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

        wcr.blocks[wcr.currentPath.internalPath()][blockNames[j]] = {
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

  function sendRequest(internalURLObject, callback) {
    var tmp = $.extend(true , {}, internalURLObject);  //TODO: remove reliance on jQuery;
    tmp.params['_wrapper_format'] = 'drupal_components';
    $.ajax({
      method: 'GET',
      url: baseURL + tmp.pathname,
      data: tmp.params,
    }).done(function(result) {
      console.log('success');
      //console.log(result);
      callback(result, internalURLObject);
    }).fail(function(e){
      console.log('error');
      navigateNormalTo(internalURLObject);
    });
  }

  function navigateTo(newPathObject) {
    console.log('[WCR] Navigating to ' + newPathObject.internalPath());
    sendRequest(newPathObject, function(tmp, newPathObject) {
      // Stop if the theme is not supported
      if (tmp['activeTheme'] != 'polymer') {
        navigateNormalTo(newPathObject);
        return;
      }



      var r = tmp['regions'];
      var regionNames = Object.keys(r);
      wcr.blocks[newPathObject.internalPath()] = {};
      for (var i = 0; i < regionNames.length; ++i) {
        var blockNames = Object.keys(r[regionNames[i]]);
        for (var j = 0; j < blockNames.length; ++j) {
          var elementId = regionNames[i] + '/' + blockNames[j];
         // var link = importElementFromURL(elementId, newPath);
          var elementNew = document.createElement(r[regionNames[i]][blockNames[j]]['element_name']);
          wcr.blocks[newPathObject.internalPath()][blockNames[j]] = {
            region: regionNames[i],
            block: blockNames[j],
            tagname: r[regionNames[i]][blockNames[j]]['element_name'],
            hash: r[regionNames[i]][blockNames[j]]['hash'],
            //link: link,
          };

          if (wcr.blocks[wcr.currentPath.internalPath()][blockNames[j]] == null ){
            //NEW
            var result = commandNew(wcr.blocks[newPathObject.internalPath()][blockNames[j]],
                                    wcr.blocks[newPathObject.internalPath()][blockNames[j-1]],  //previous block
                                    newPathObject);
            wcr.blocks[newPathObject.internalPath()][blockNames[j]]['link'] = result['link'];
            wcr.blocks[newPathObject.internalPath()][blockNames[j]]['element'] = result['element'];
            console.log('[WCR] New block: ' + blockNames[j]);
          } else if (wcr.blocks[wcr.currentPath.internalPath()][blockNames[j]]['hash'] != wcr.blocks[newPathObject.internalPath()][blockNames[j]]['hash']) {
            //UPDATE
            var result = commandUpdate(wcr.blocks[wcr.currentPath.internalPath()][blockNames[j]],
                                       wcr.blocks[newPathObject.internalPath()][blockNames[j]],   //old block
                                       newPathObject);
            wcr.blocks[newPathObject.internalPath()][blockNames[j]]['link'] = result['link'];
            wcr.blocks[newPathObject.internalPath()][blockNames[j]]['element'] = result['element'];
            console.log('[WCR] Updated block: ' + blockNames[j]);
          } else {
            //SAME
            wcr.blocks[newPathObject.internalPath()][blockNames[j]]['link'] = wcr.blocks[wcr.currentPath.internalPath()][blockNames[j]]['link'];
            wcr.blocks[newPathObject.internalPath()][blockNames[j]]['element'] = wcr.blocks[wcr.currentPath.internalPath()][blockNames[j]]['element'];
          }
        }
      }
      //TODO: remove blocks
      var oldBlockList = Object.keys(wcr.blocks[wcr.currentPath.internalPath()]);
      for (var i = 0; i < oldBlockList.length; ++i) {
        if (wcr.blocks[newPathObject.internalPath()][oldBlockList[i]] == null) {
          // REMOVE
          commandDelete(wcr.blocks[wcr.currentPath.internalPath()][oldBlockList[i]]);
          console.log('[WCR] removed block: ' + oldBlockList[i]);
        }
      }

      wcr.currentPath = newPathObject;
      history.pushState({}, document.title, newPathObject.internalPath());
    });

  }

  function removeAllElements() {
    for (var i = 0; i < wcr.blocks.length; ++i) {
      wcr.blocks[i].element.remove();
    }
    wcr.blocks = {};
  }

  /* Helpers */
  function Url(url) {
    var link = document.createElement('a');
    link.href = url;
    var fragmentLength = link.hash.length;
    this.absoluteUrl = link.href;
    if (fragmentLength < 2) {
      this.requestUrl = this.absoluteUrl;
    }
    else {
      this.requestUrl = this.absoluteUrl.slice(0, -fragmentLength);
      this.fragment = link.hash.slice(1);
    }
    this.pathname = link.pathname;
    this.params = QueryStringToHash(link.search.substring(1)) || {};
    this.internalPath = function () {
      // pathname + parameters
      if (Object.keys(this.params).length > 0) {
        return this.pathname + '?' + $.param(this.params);
      } else {
        return this.pathname;
      }
      //TODO: remove reliance on jQuery
    };
    this.href = function () {
      return this.baseUrl() + this.internalPath();
    }

    this.baseUrl = function (){
      var pathArray = link.href.split( '/' );
      var protocol = pathArray[0];
      var host = pathArray[2];
      var url = protocol + '//' + host;
      return url;
    };
    this.setInternalPath = function(internalPath) {

    };
  }

  function getCurrentInternalURL() {
    return new Url(location.href);
  }

  function navigateNormalTo(url) {
    window.location.href = url.href();
  }

  function QueryStringToHash(query) {
    if (query == '')
      return {};
    var hash = {};
    var vars = query.split("&");
    for (var i = 0; i < vars.length; i++) {
      var pair = vars[i].split("=");
      var k = decodeURIComponent(pair[0]);
      var v = decodeURIComponent(pair[1]);

      // If it is the first entry with this name
      if (typeof hash[k] === "undefined") {
        if (k.substr(k.length-2) != '[]')  // not end with []. cannot use negative index as IE doesn't understand it
          hash[k] = v;
        else
          hash[k] = [v];
        // If subsequent entry with this name and not array
      } else if (typeof hash[k] === "string") {
        hash[k] = v;  // replace it
        // If subsequent entry with this name and is array
      } else {
        hash[k].push(v);
      }
    }
    return hash;
  };

  function isAdminUrl(url) {
    var path = url.internalPath();
    return path.startsWith('/admin');
  }

  function isSpecialUrl(url) {
    //TODO: Simplify this function
    var path = url.internalPath();
    return path.startsWith('/user/logout');
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
    navigateNormalTo: navigateNormalTo,
    currentPath: currentPath,
  };

  /* First page load */
  if (drupalSettings.componentsBlockList) {
    wcr.currentPath = getCurrentInternalURL();  //Url Object
    wcr.loadFromMetadata();

    /* Attach event to links */
    $('body').on('click', 'a', function (event) {
      //Middle click, cmd click, and ctrl click should open links as normal.
      if (event.which > 1 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
        return;
      }
      //event.preventDefault();
      console.log(event);
      var target = new Url(event.currentTarget.href);
      if (!isAdminUrl(target) && !isSpecialUrl(target) && target.baseUrl() == wcr.currentPath.baseUrl()) {
        if (target.params['_wrapper_format'] == 'drupal_block') {
          delete(target.params['_wrapper_format']);
          if (target.params['mode']) delete(target.params['mode']);
          if (target.params['block']) delete(target.params['block']);
        }

        event.preventDefault();
        if (target.internalPath() == wcr.currentPath.internalPath()) {
          console.log('[WCR] Same path, not navigating.');
          return;
        }
        wcr.navigateTo(target);
      }
    });
  } else {
    console.log('WCR not enabled.');
  }
}(jQuery));