# Renderer(s) for Web Component experiments
## BlockRenderer
BlockRenderer aims to render any block on any page on request. For example, you may choose to render the Main Content block on the frontpage, or the Menu block on `/admin`. 

Combined with ShadowDOM, this allows developers and users to export their Drupal blocks and embed them on external sites.

### Test steps
 - Install and enable this module.
 - Visit any page, put `?_wrapper_format=drupal_block` at the end of the URL.
 - You should see a list of blocks in the page.
 - Copy one of the names of the blocks.
 - Add `&block={{block}}` to the URL in your browser. (Note that the parameter should be urlencoded, such as `admin/?_wrapper_format=drupal_block&block=content%2Fseven_content`)
 - You should see only the block you requested.
 
### Test steps (Embedding on Page)
 - Enable this module.
 - Install theme `integrity` and set as default. (This theme has better written CSS and therefore is used for demonstration) 
 - Visit `http://siteurl/modules/wcr/export/test.html`. 
 - You should see a few blocks ("Branding", "Content", "Main Menu") embedded on the static page.
 
### Current Limitations
  - All assets on the page are loaded (even though not needed by a block).
