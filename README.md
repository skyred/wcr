# Web Components Renderer
WCR is a collection of enhancements to Drupal 8 UX using Web Components.

<!-- START doctoc generated TOC please keep comment here to allow auto update -->
<!-- DON'T EDIT THIS SECTION, INSTEAD RE-RUN doctoc TO UPDATE -->
## Features

- [Features](#features)
  - [BlockRenderer](#blockrenderer)
  - [Non-refresh Navigation with Componentized Blocks](#non-refresh-navigation-with-componentized-blocks)

<!-- END doctoc generated TOC please keep comment here to allow auto update -->
## Features

### BlockRenderer
BlockRenderer aims to render any block on any page on request. For example, you may choose to render the Main Content block on the frontpage, or the Menu block on `/admin`. 

Combined with ShadowDOM, this allows developers and users to export their Drupal blocks and embed them on external sites.

See [Documentation](.docs/block-renderer.md)

Extensibility via Plugins is in progress.

### Componentized Pages (CoPage)

Non-refresh Navigation with Componentized Blocks.

Somewhat equivalent to RefreshLess + BigPipe.

See [Documentation](.docs/AjaxNavigation.md)

## Roadmap
 - Plugins (HTMLMainContentFormatter)
 - A controller for building Lazybuilder-built content (like Fragments in Symfony; need security measures)
 - A unified API for requesting page partials (including Lazybuilder) - use sub-request to reserve-proxy to 
 respective handlers.
 - A CUSTOM ELEMENT for embedding
 - UI for exporting block
 - 