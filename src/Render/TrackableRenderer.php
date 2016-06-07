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

  /**
   * @param $elements
   * @param bool $is_root_call
   */
  protected function doRender(&$elements, $is_root_call = FALSE) {
    \Drupal::service("wcr.callstack")->append(array(
      'func' => 'doRender',
      'element' => $elements,
    ));
    parent::doRender($elements, $is_root_call);
    debug(\Drupal::service("wcr.callstack")->printStack());
    \Drupal::service("wcr.callstack")->pop();
  }
}