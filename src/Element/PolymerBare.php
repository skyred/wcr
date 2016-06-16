<?php
/**
 * @file
 * Contains \Drupal\wcr\Element\PolymerBare.
 */

namespace Drupal\wcr\Element;
use Drupal\Core\Render\Element\RenderElement;

/**
 * Provides a render element for an entire HTML page: <html> plus its children.
 *
 * @RenderElement("polymerbare")
 */
class PolymerBare extends RenderElement {

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    return array(
      '#theme' => 'polymerbare',
      '#element_name' => '',
    );
  }

}
