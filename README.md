# Web Components Renderer

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

### Non-refresh Navigation with Componentized Blocks

See [Documentation](.docs/AjaxNavigation.md)