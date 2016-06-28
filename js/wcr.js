(function($, drupalSettings) {
  "use strict";

  /**
   * Assert function.
   * Credit: http://stackoverflow.com/questions/15313418/javascript-assert
   */
  function assert(condition, message) {
    if (!condition) {
      message = message || "Assertion failed";
      if (typeof Error !== "undefined") {
        throw new Error(message);
      }
      throw message; // Fallback
    }
  }

  /**
   * Definition of Url class.
   *
   * A Url has three parts:
   *  - path
   *  - params
   *  - fragment
   *
   * @param url
   * @constructor
   */
  var Url = function (url) {
    this.pathname = '';
    this.params = {};
    this.fragment = '';

    var link = document.createElement('a');
    link.href = url;
    var fragmentLength = link.hash.length;
    this.absoluteUrl = link.href;
    if (fragmentLength >= 2) {
      this.fragment = link.hash.slice(1);
    }
    this.pathname = link.pathname;
    this.params = QueryStringToHash(link.search.substring(1)) || {};
  };

  Url.prototype.internalPath = function () {
    // pathname + parameters
    var ret = this.pathname;
    if (Object.keys(this.params).length > 0) {
      ret = ret + '?' + $.param(this.params);
    }
    return ret;
    //TODO: remove reliance on jQuery
  };

  Url.prototype.baseUrl = function (){
    return drupalSettings.path.baseUrl;
  };

  Url.prototype.isAdmin = function () {
    var path = this.internalPath();
    return path.startsWith('/admin');
  };

  Url.prototype.isSpecialPath = function () {
    var path = this.internalPath();
    return path.startsWith('/user/logout');
  };

  Url.prototype.fullUrl = function () {
    var ret = this.baseUrl() + this.internalPath();
    if (this.fragment.length > 0) {
      ret = ret + '#' + this.fragment;
    }
  };

  Url.prototype.toString = function () {
    return this.fullUrl();
  };

  /**
   * Definition of Region class.
   * @param name
   * @constructor
   */
  var Region = function (name, element) {
    this.name = name;
    this.element = element;
  };

  Region.prototype.associateElement = function (node) {
    this.element = node;
  };

  Region.prototype.toString = function () {
    return this.name;
  };

  /**
   * Definition of RegionList
   * @constructor
   */
  var RegionList = function () {
    this.regions = [];
    this.regionCount = 0;
    this.map = [];
  };

  /**
   * Find regions on first page load.
   */
  RegionList.prototype.findRegions = function () {
    var tmp = JSON.parse(drupalSettings.componentsBlockList);
    var regionNames = Object.keys(tmp['regions']);
    for (var i = 0; i < regionNames.length; ++i) {
      var regionElement = $("[data-components-display-region='" + regionNames[i] + "']")[0].parentNode;
      this.regions.push(new Region(regionNames[i], regionElement));
      this.map[regionNames[i]] = this.regions[i];
      ++this.regionCount;
    }
  };

  RegionList.prototype.get = function (regionName) {
    return this.map[regionName];
  };

  RegionList.prototype.listAll = function (regionName) {
    return Object.keys(this.map);
  };

  /**
   * Definition of Block class
   */
  var Block = function (name, data) {
    this.blockName = name;
    this.elementName = data.elementName || '';
    this.contextHash = data.contextHash || '';
    this.region = data.region || undefined;
    this.page = data.pageState || wcr.historyStack.getCurrentState() || undefined; // Associated PageState object
    this.importLink = undefined;
    this.element = data.element || undefined;
  };

  /**
   * Get the url for retrieving a componentized Block from BlockRenderer;
   */
  Block.prototype.getBlockUrl = function () {
    assert(this.page instanceof PageState, 'PageState not specified for Block ' + this.blockName + '.');

    // Make a copy of the url
    var tmpUrl = $.extend(true, {} ,this.page.url); //TODO: remove reliance on jQuery
    tmpUrl.params['_wrapper_format'] = 'drupal_block';
    tmpUrl.params['block'] = this.region + '/' + this.blockName;
    tmpUrl.params['mode'] = 'bare';
    return tmp.fullUrl();
  };

  Block.prototype.doImport = function () {
    assert(this.page instanceof PageState, 'PageState not specified for Block ' + this.blockName + '.');

    var url = this.getBlockURL();
    var link = document.createElement('link');
    link.rel = 'import';
    link.href = url;
    this.importLink = document.head.appendChild(link);
    return this.importLink;
  };

  Block.prototype.findOnPage = function () {
    return this.element = $(this.elementName)[0];
  };

  Block.prototype.associateWith = function (node) {
    this.element = node;
    return this.element;
  };

  Block.prototype.removeImport = function () {
    assert(this.importLink != undefined, 'Block ' + this.blockName + ' doesn\'t have import links associated.');
    document.head.removeChild(this.importLink);
    this.importLink = undefined;
  };

  Block.prototype.removeFromPage = function () {
    assert(this.element, 'Block ' + this.blockName + ' is not on the page.');
    this.element.remove();
    this.element = undefined;
  };

  Block.prototype.placeBlockByReplace = function (node) {
    assert(this.elementName.length > 0, 'Element name not specified for Block ' + this.blockName + '.');

    var elementNew = document.createElement(this.elementName);
    node.parentNode.replaceChild(elementNew, node);
    this.element = elementNew;
    return this.element;
  };

  Block.prototype.placeBlockAfter = function (node) {
    assert(this.elementName.length > 0, 'Element name not specified for Block ' + this.blockName + '.');

    var elementNew = document.createElement(this.elementName);
    //@todo
  };

  /**
   * Definition of PageState class.
   * @constructor
   */
  var PageState = function (urlObject) {
    this.url = urlObject;
    this.hashSuffix = ''; // Hash of the url, generated by the server
    //this.regionList = [];
    this.blockList = [];  // List of blocks on the page
    this.jsAssets = [];
  };

  /**
   * Get metadata from the server about the blocks on the page.
   */
  PageState.prototype.getMetadata = function () {

  };

  PageState.prototype.constructFromMetadata = function (data) {
    if (typeof data == 'string') {
      data = JSON.parse(data);
    }
    var regions = data['regions'];
    var regionNames = Object.keys(regions);
    this.blockList = [];
    for (var i = 0; i < regionNames.length; ++i) {
      var blockNames = Object.keys(regions[regionNames[i]]);
      for (var j = 0; j < blockNames.length; ++j) {
        this.blockList.push(new Block(blockNames[j], {
          region: RegionList.get(regionNames[i]),
          elementName: regions[regionNames[i]][blockNames[j]]['element_name'],
          contextHash: regions[regionNames[i]][blockNames[j]]['hash'],
        }));
      }
    }
  };

  PageState.prototype.toString = function () {
    return PageState.url.toString();
  };

  /**
   * Definition of HistoryStack
   * @constructor
   */
  var HistoryStack = function () {
    this.count = 0;
    this.stack = [];
  };

  HistoryStack.prototype.push = function (newState) {
    assert(newState instanceof PageState, 'Not a valid PageState object.');
    this.stack.push(newState);
    this.count++;
  };

  HistoryStack.prototype.getCurrentState = function () {
    return this.stack[this.stack.length];
  };


  /**
   * Definition of Controller
   */

  var Controller = function () {
    this.baseUrl = drupalSettings.path.baseURI;
    this.historyStack = new HistoryStack();
    this.regionList = new RegionList();

  };

  Controller.prototype.commandUpdate = function (oldElement, newElement) {
    assert(oldElement instanceof Block && newElement instanceof Block, 'Invalid types.');
    oldElement.removeImport();
    newElement.doImport();
    newElement.placeBlockByReplace(oldElement.element);
    // No need to remove old element because it is already replaced.
  };

  Controller.prototype.commandDelete = function(oldElement) {
    oldElement.removeImport();
    oldElement.removeFromPage();
  };

  Controller.prototype.commandNew = function(newElement, previousElement, newPathObject) {
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
  };

  Controller.prototype.firstPageLoad = function () {
    this.regionList.findRegions();

    var currentState = new PageState();
    currentState.constructFromMetadata(drupalSettings.componentsBlockList);
    currentState.setUrl(new Url(window.location.href));

    for (var i = 0; i < currentState.blockList.length; ++i) {
      currentState.blockList[i].findOnPage();
      currentState.blockList[i].doImport();
    }

    this.attachDrupalBehaviors();
  };

  Controller.prototype.attachDrupalBehaviors = function () {
    //@todo per shadowdom
    Drupal.attachBehaviors(document);
  };


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
    return $.ajax({
      method: 'GET',
      url: tmp.fullUrl(),
      data: tmp.params,
    });/*.done(function(result) {
      console.log('success');
      //console.log(result);
      callback(result, internalURLObject);
    }).fail(function(e){
      console.log('error');
      navigateNormalTo(internalURLObject);
    });*/
  }

  function navigateTo(newPathObject) {
    console.log('[WCR] Navigating to ' + newPathObject.internalPath());
    sendRequest(newPathObject, function(tmp, newPathObject) {
      // Redirect response
      if (tmp['redirect'] != null) {
        window.location.href = tmp['redirect'];
        return;
      }
      // Stop if the theme is not supported
      if (tmp['activeTheme'] != 'polymer') {
        navigateNormalTo(newPathObject);
        return;
      }

      var title = tmp['title'];
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
      //document.title = title;
      reattachBehaviors();
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
    reattchBehaviors: reattachBehaviors,
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
      if (/*!isAdminUrl(target) && !isSpecialUrl(target) &&*/ target.baseUrl() == wcr.currentPath.baseUrl()) {
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
}(jQuery, drupalSettings));