# Renderer(s) for Web Component experiments

## PartialRenderer

### Test steps
 - Apply `core.patch` (this is copied verbatim from the core patch in RefressLess)
 - Install this module, and `devel` module
 - Visit any page, put `?_wrapper_format=drupal_partial` at the end of the URL
 - You should see 1. a render array of the page 2. metadata from the render array


## BlockRenderer

### Test steps
 - Apply `core.patch` (this is copied verbatim from the core patch in RefressLess)
 - Install this module, and `devel` module
 - Visit any page, put `?_wrapper_format=drupal_block` at the end of the URL
 - You should see a list of blocks in the page
 - Copy one of the names of the blocks
 - Add `&block={{block}}` to the URL in your browser (Note that the parameter should be urlencoded, such as `admin/?_wrapper_format=drupal_block&block=content%2Fseven_content`)
 - You should see only the block you requested
 
### Current Limitations
  - All assets on the page are loaded (even though not needed by a block)
  - Placeholders are not completely replaced, resulting in some blocks (e.g "status message") having no content (This is a bug and will be fixed soon.)
