# Renderer(s) for Web Component experiments
## BlockRenderer
BlockRenderer aims to render any block on any page on request. For example, you may choose to render the Main Content block on the frontpage, or the Menu block on `/admin`. 

Combined with ShadowDOM, this allows developers and users to export their Drupal blocks and embed them on external sites.

### Test steps
 - Install and enable `twig_polymer` and this module. 
 - Visit any page, put `?_wrapper_format=drupal_block` at the end of the URL.
 - You should see a list of blocks in the page.
 - Copy one of the names of the blocks.
 - Add `&block={{block}}` to the URL in your browser. (Note that the parameter should be urlencoded, such as `admin/?_wrapper_format=drupal_block&block=content%2Fseven_content`)
 - You should see only the block you requested.
 - Add `&element=element-name` to the URL, and view the source of the resulting page. You should see the result is wrapped as a Polymer element.

### Test steps (Embedding on Page)
 - Enable this module.
 - Set theme `bartik` as default. 
 - Visit `http://siteurl/modules/wcr/export/test2.html`. 
 - You should see the main content block embedded on the static page.

### How to embed a block on a static page
 - Include the polyfill in `<head>`
```
<script src="/modules/twig_polymer/bower_components/webcomponentsjs/webcomponents-lite.min.js?v=1.x"></script>
```
 - In `<head>` also import the element for the block:
```
<link rel="import" href="/?_wrapper_format=drupal_block&block=content%2Fbartik_content&element_name=bartik-content"></link>
<!-- Will simplify this to only one argument -->
```
 - In `<body>` of your static page, use the element you just imported:
```
<bartik-content></bartik-content> 
```

### Limitations
 - CSS and JS doesn't work for custom themes yet.
