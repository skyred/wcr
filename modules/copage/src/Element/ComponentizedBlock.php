<?php
/**
 * @file
 * Contains \Drupal\copage\Element\ComponentizedBlock.
 */

namespace Drupal\copage\Element;
use Drupal\Core\Render\Element\RenderElement;

/**
 * Provides a tag for using the Componentized Block.
 *
 * @RenderElement("componentized_block")
 */
class ComponentizedBlock extends RenderElement {

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    return array(
      '#theme' => 'componentized_block',
      '#element_name' => '',
    );
  }

}
