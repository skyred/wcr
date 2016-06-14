<?php
/**
 * @file
 * Contains \Drupal\wcr\Element\BodyOnly.
 */

namespace Drupal\wcr\Element;
use Drupal\Core\Render\Element\RenderElement;

/**
 * Provides a render element for an entire HTML page: <html> plus its children.
 *
 * @RenderElement("polymer")
 */
class Polymer extends RenderElement {

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    return array(
      '#theme' => 'polymer',
      '#element_name' => '',
    );
  }

}
