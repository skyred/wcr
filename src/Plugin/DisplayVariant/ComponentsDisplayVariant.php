<?php

/**
 * @file
 * Contains \Drupal\wcr\Plugin\DisplayVariant\ComponentsDisplayVariant.
 */

namespace Drupal\wcr\Plugin\DisplayVariant;
use Drupal\block\Plugin\DisplayVariant\BlockPageVariant;
use Drupal\wcr\Service\Utilities;

use Drupal\Core\Render\Element;


/**
 * Provides a variant plugin that contains Componentized blocks (as custom element tags).
 *
 * @DisplayVariant(
 *   id = "components_display",
 *   admin_label = @Translation("Componentized Block page")
 * )
 */
class ComponentsDisplayVariant extends BlockPageVariant {
  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = parent::build();

    // Set default page cache keys that include the display.
    $build['#cache']['keys'] = [
      'components_display',
      $this->id(),
    ];

    foreach (Element::children($build) as $region) {
      $blocks = Element::children($build[$region]);
      $build[$region]['components_display_region_wrapper'] = ['#markup' => '<div data-components-display-region></div>'];

      foreach ($blocks as $key) {
        $build[$region][$key] = [
          '#theme' => 'componentized_block',
          '#element_name' => Utilities::replaceUnderscore($key),
          '#weight' => $build[$region][$key]['#weight'],
          '#cache' => $build[$region][$key]['#cache'],
          //TODO attachments
        ];
        $build[$region][$key]['#cache']['keys'][] = ['components_display', $this->id(), 'block', $key];
      }
    }

    return $build;
  }

}
