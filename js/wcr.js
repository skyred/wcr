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

  Url.prototype.baseUrl = function () {
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

  RegionList.prototype.listAll = function () {
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

    var url = this.getBlockUrl();
    var link = document.createElement('link');
    link.rel = 'import';
    link.href = url;
    this.importLink = document.head.appendChild(link);
    return this.importLink;
  };

  Block.prototype.findOnPage = function () {
    return this.element = $(this.elementName)[0];
  };

  Block.prototype.associateWith = function (oldBlock) {
    this.element = oldBlock.element;
    this.importLink = oldBlock.importLink;
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

  Block.prototype.placeBlockAfter = function (node, controller) {
    assert(controller instanceof Controller, 'Invalid type.');
    assert(this.elementName.length > 0, 'Element name not specified for Block ' + this.blockName + '.');

    var elementNew = document.createElement(this.elementName);
    controller.regionList.get(this.region).element.insertBefore(elementNew, node.nextSibling);
    // Insert before nextSibling == insert after
    this.element = elementNew;
    return this.element;
  };

  Block.prototype.toString = function () {
    return this.blockName;
  };

  /**
   * Definition of PageState class.
   * @constructor
   */
  var PageState = function (urlObject) {
    this.url = urlObject;
    this.hashSuffix = ''; // Hash of the url, generated by the server
    //this.regionList = [];
    this.blockList = [];  // List of blocks on the page; one list per region
    this.jsAssets = [];
  };

  /**
   * Get metadata from the server about the blocks on the page.
   */
  PageState.prototype.getMetadata = function () {
    var tmp = $.extend(true , {}, this.url);  //TODO: remove reliance on jQuery;
    tmp.params['_wrapper_format'] = 'drupal_components';
    return $.ajax({
      method: 'GET',
      url: tmp.fullUrl(),
      data: tmp.params,
    });
  };

  /**
   * Construct Block List from metadata retrieved from the server.
   * Such metadata could be initially stored on the page via drupalSettings,
   * or fetched by XHR.
   * @param data
   * @param controller
   */
  PageState.prototype.constructFromMetadata = function (data, controller) {
    if (typeof data == 'string') {
      data = JSON.parse(data);
    }
    var regions = data['regions'];
    var regionNames = Object.keys(regions);
    assert(regionNames.length = controller.regionList.listAll().length, 'Number of regions mismatch.');
    this.blockList = [];
    for (var i = 0; i < regionNames.length; ++i) {
      this.blockList[regionNames[i]] = {};
      var blockNames = Object.keys(regions[regionNames[i]]);
      for (var j = 0; j < blockNames.length; ++j) {
        this.blockList[regionNames[i]][blockNames[j]] = (new Block(blockNames[j], {
          region: controller.regionList.get(regionNames[i]),
          elementName: regions[regionNames[i]][blockNames[j]]['element_name'],
          contextHash: regions[regionNames[i]][blockNames[j]]['hash'],
        }));
      }
    }
  };

  PageState.prototype.toString = function () {
    return PageState.url.toString();
  };

  PageState.prototype.getBlock = function (blockName) {
    var regionNames = Object.keys(this.blockList);
    for (var i = 0; i < regionNames.length; ++i) {
      if (this.blockList[regionNames[i]][blockName] != undefined) {
        return this.blockList[regionNames[i]][blockName];
      }
    }
    return undefined;
  };

  PageState.prototype.listAllBlocks = function () {
    var ret = [];
    var regionNames = Object.keys(this.blockList);
    for (var i = 0; i < regionNames.length; ++i) {
      var blockNames = Object.keys(this.blockList[regionNames[i]])
      for (var j = 0; j < blockNames.length; ++j) {
        ret.push(this.blockList[regionNames[i]][blockNames[j]]);
      }
    }
    return ret;
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
    this.currentState = undefined;
    // We assume that for each Controller, the region list is unchanged. This means the theme cannot change for each
    // controller. If the theme changes, the page has to refresh to create a new Controller.

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

  Controller.prototype.commandNew = function(newElement, previousElement) {
    assert(newElement instanceof Block, 'Invalid type.');

    if (previousElement == null) {
      var previousNode = this.regionList.get(newElement.region).element.firstChild;
    } else {
      var previousNode = previousElement.element;
    }

    newElement.placeBlockAfter(previousNode, this);
  };

  Controller.prototype.firstPageLoad = function () {
    this.regionList.findRegions();

    var currentState = new PageState();

    currentState.url = (new Url(window.location.href));
    this.historyStack.push(currentState);
    this.currentState = currentState;

    currentState.constructFromMetadata(drupalSettings.componentsBlockList, this);

    for (var i = 0; i < currentState.length; ++i) {
      currentState.blockList[i].findOnPage();
      currentState.blockList[i].doImport();
    }

    this.attachDrupalBehaviors();
  };

  Controller.prototype.attachDrupalBehaviors = function () {
    //@todo per shadowdom
    Drupal.attachBehaviors(document);
  };

  Controller.prototype.navigateNormalTo = function (url) {
    if (typeof url == 'string') {
      window.location.href = url;
    } else {
      window.location.href = url.fullUrl();
    }
  };

  Controller.prototype.sendRequest = function(internalURLObject) {
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

  Controller.prototype.navigateTo = function(newPath) {
    console.log('[WCR] Navigating to ' + newPath.fullUrl());

    var newState = new PageState(newPath);
    // Fetch metadata of the new PageState
    // @todo handle same page navigation

    newState.getMetadata().done(function(metadata) {
      // Redirect response
      if (metadata['redirect'] != null) {
        this.navigateNormalTo(metadata['redirect']);
        return;
      }
      // Stop if the theme is not supported
      if (metadata['activeTheme'] != 'polymer') {
        this.navigateNormalTo(newPath);
        return;
      }

      newState.constructFromMetadata(metadata);

      var regionNames = this.regionList.listAll();
      for (var i = 0; i < regionNames.length; ++i) {
        // Diff region by region
        var currentRegion = this.regionList.get(regionNames[i]);
        var blockNames = Object.keys(newState.blockList[currentRegion.name]);
        for (var j = 0; j < blockNames.length; ++j) {
          var newBlock = newState.blockList[currentRegion.name][j];
          if (this.currentState.getBlock(newBlock.blockName) == undefined){
            // NEW
            this.commandNew(newBlock,
                            newState.blockList[currentRegion.name][blockNames[j-1]],
                            // Previous block of the new state, must already been placed on page
                            newPath);
            console.log('[WCR] New block: ' + newBlock.blockName);
          } else if (this.currentState.getBlock(newBlock.blockName)['contextHash']
                     != newBlock['contextHash']) {
            //UPDATE
            this.commandUpdate(this.currentState.getBlock(newBlock.blockName),  //old block
                               newBlock,
                               newPath);
            console.log('[WCR] Updated block: ' + newBlock.blockName);
          } else {
            //SAME
            newBlock.associateWith(this.currentState.getBlock(newBlock.blockName));
          }
        }
      }
      //TODO: remove blocks
      var oldBlockList = this.currentState.listAllBlocks();
      for (var i = 0; i < oldBlockList.length; ++i) {
        if (newState.getBlock(oldBlockList[i].name) == null) {
          // REMOVE
          this.commandDelete(oldBlockList[i]);
          console.log('[WCR] removed block: ' + oldBlockList[i].blockName);
        }
      }

      this.historyStack.push(newState);
      this.currentState = this.historyStack.getCurrentState();
      //document.title = title;
      this.attachDrupalBehaviors();
      history.pushState({}, document.title, newPath.fullUrl());
    }.bind(this));

  };

  Controller.prototype.bindEvents = function () {
    /* Attach event to links */
    $('body').on('click', 'a', function (event) {
      // Middle click, cmd click, and ctrl click should open links as normal.
      if (event.which > 1 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
        return;
      }
      // event.preventDefault();
      console.log(event);
      var target = new Url(event.currentTarget.href);
      if (/*!isAdminUrl(target) && !isSpecialUrl(target) &&*/ target.baseUrl() == this.currentState.baseUrl()) {
        if (target.params['_wrapper_format'] == 'drupal_block') {
          delete(target.params['_wrapper_format']);
          if (target.params['mode']) delete(target.params['mode']);
          if (target.params['block']) delete(target.params['block']);
        }

        event.preventDefault();
        if (target.internalPath() == this.currentState.internalPath()) {
          console.log('[WCR] Same path, not navigating.');
          return;
        }
        this.navigateTo(target);
      }
    }.bind(this));
  };

  /* Helpers */

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
    controller : new Controller(),
  };

  /* First page load */
  if (drupalSettings.componentsBlockList) {
    wcr.controller.firstPageLoad();
  } else {
    console.log('WCR not enabled.');
  }
}(jQuery, drupalSettings));