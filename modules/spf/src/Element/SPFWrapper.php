<?php
/**
 * @file
 * Contains \Drupal\wcr\Element\WcrHtml.
 */

namespace Drupal\spf\Element;
use Drupal\Core\Render\Element\RenderElement;

/**
 * Provides a render element for an entire HTML page: <html> plus its children.
 *
 * @RenderElement("spf_wrapper")
 */
class SPFWrapper extends RenderElement {

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    return array(
      '#theme' => 'spf_wrapper',
      'id' => '',
    );
  }

}
