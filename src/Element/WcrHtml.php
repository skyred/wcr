<?php

namespace Drupal\wcr\Element;
use Drupal\Core\Render\Element\RenderElement;

/**
 * Provides a render element for an entire HTML page: <html> plus its children.
 *
 * @RenderElement("wcrhtml")
 */
class WcrHtml extends RenderElement {

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    return array(
      '#theme' => 'wcrhtml',
      // HTML5 Shiv
      '#attached' => array(
        'library' => array('core/html5shiv'),
      ),
    );
  }

}
