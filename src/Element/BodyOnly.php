<?php

namespace Drupal\wcr\Element;
use Drupal\Core\Render\Element\RenderElement;

/**
 * Provides a render element for an entire HTML page: <html> plus its children.
 *
 * @RenderElement("bodyonly")
 */
class BodyOnly extends RenderElement {

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    return array(
      '#theme' => 'bodyonly',
    );
  }

}
