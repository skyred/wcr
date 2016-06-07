<?php

namespace Drupal\wcr\Render;

use Drupal\Core\Access\AccessResultInterface;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Render\BubbleableMetadata;
use Drupal\Core\Render\Element;
use Drupal\Core\Render\Markup;
use Drupal\Core\Render\Renderer;
use Drupal\Core\Render\RenderContext;

/**
 * Class RecursiveRenderer
 *
 * Override the Renderer in core to leave recursive structure in render array.
 */
class TrackableRenderer extends Renderer {
  
 protected function doRender(&$elements, $is_root_call = FALSE) {
    parent::doRender($elements, $is_root_call);
  }
}