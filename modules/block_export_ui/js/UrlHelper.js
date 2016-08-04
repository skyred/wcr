(function($){

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
    var pathArray = location.href.split( '/' );
    var protocol = pathArray[0];
    var host = pathArray[2];
    var url = protocol + '//' + host;
    return url + drupalSettings.path.baseUrl.slice(0,-1);
  };

  Url.prototype.isAdmin = function () {
    var path = this.internalPath();
    return path.startsWith('/admin');
  };

  Url.prototype.fullUrl = function () {
    var ret = this.baseUrl() + this.internalPath();
    if (this.fragment.length > 0) {
      ret = ret + '#' + this.fragment;
    }
    return ret;
  };

  Url.prototype.toString = function () {
    return this.fullUrl();
  };
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
  window.drupalUrlHelper = window.drupalUrlHelper ||{};
  drupalUrlHelper.Url = Url;
}(jQuery));