# Web Components Renderer 
[![Build Status](https://travis-ci.org/ztl8702/wcr.svg?branch=8.x-1.x)](https://travis-ci.org/ztl8702/wcr)

WCR is a collection of enhancements to Drupal 8 UX using Web Components.

<!-- START doctoc generated TOC please keep comment here to allow auto update -->
<!-- DON'T EDIT THIS SECTION, INSTEAD RE-RUN doctoc TO UPDATE -->
## Features

- [Features](#features)
  - [Render a block or specfic parts of the page](#render-a-block-or-specfic-parts-of-the-page)
  - [Componentized Pages (CoPage)](#componentized-pages-copage)
- [Roadmap](#roadmap)

<!-- END doctoc generated TOC please keep comment here to allow auto update -->
## Features

### Render a block or specfic parts of the page
WCR offers a ender any block on any page on request. For example, you may choose to render the Main Content block on the frontpage, or the Menu block on `/admin`. 

Combined with ShadowDOM, this allows developers and users to export their Drupal blocks and embed them on external sites.

 - Block List: a list of blocks on the page requested, with their cache context. Primarily for debug purposes
   - Usage: add `?_wrapper_format=drupal_wcr&_wcr_mode=list` to request URL.
 - Single Block: outputs an HTML page, but only with one block. This can be used for embedding via `<iframe>`.
   - Usage: add `?_wrapper_format=drupal_wcr&_wcr_mode=singleblock&_wcr_block={Block ID}` to request URL. Use the Block ID you see in the output using the `Block List` format.
 - Polymer Element: wraps a block as a Polymer element definition, so that it can be embedded on pages via HTML Import and Custom Element tags. This also allows for refreshless-like navigation which I introduced in a previous post. 
   - Usage: add `?_wrapper_format=drupal_wcr&_wcr_mode=lis&_wcr_block={Block ID}t` to request URL.
 - SPF: outputs JSON response compatible with Youtube's Structured Page Fragments framework. This can also be used to acheive refreshless-like navigation. 
   - Usage: add `?_wrapper_format=drupal_wcr&_wcr_mode=spf` to request URL.

YOu can also create your own format via Plugin:
 - Implement [`RenderArrayFormatterInterface`](src/Plugin/wcr/RenderArrayFormatterInterface.php)
 - Annotate with `@RenderArrayFormatter`

### Componentized Pages (CoPage)

Non-refresh Navigation with Componentized Blocks.

Somewhat equivalent to RefreshLess + BigPipe.

See [Blog Post](https://blog.radiumz.org/en/post/33/gsoc-2016-more-app-experience-drupal) | [Documentation](.docs/AjaxNavigation.md)

## Roadmap
 - A controller for building Lazybuilder-built content (like Fragments in Symfony; need security measures)
 - A CUSTOM ELEMENT for embedding
 - UI for exporting block
