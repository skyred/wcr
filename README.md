# Renderer(s) for Web Component experiments

## PartialRenderer

### Test steps
 - Apply `core.patch` (this is copied verbatim from the core patch in RefressLess)
 - Install this module, and `devel` module
 - Visit any page, put `?_wrapper_format=drupal_partial` at the end of the URL
 - You should see 1. a render array of the page 2. metadata from the render array.