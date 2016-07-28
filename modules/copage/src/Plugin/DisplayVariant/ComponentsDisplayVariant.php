<?php

/**
 * @file
 * Contains \Drupal\copage\Plugin\DisplayVariant\ComponentsDisplayVariant.
 *
 * Provides a variant plugin that contains Componentized blocks (as custom element tags).
 */

namespace Drupal\copage\Plugin\DisplayVariant;
use Drupal\block\Plugin\DisplayVariant\BlockPageVariant;
use Drupal\wcr\PageState;
use Drupal\wcr\Service\Utilities;

use Drupal\Core\Render\Element;


/**
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
      $build[$region]['components_display_region_wrapper'] = ['#markup' => '<div data-components-display-region="' . $region . '"></div>'];

      foreach ($blocks as $key) {
        \Drupal::service('renderer')->renderRoot($build[$region][$key]);
        $weight = isset($build[$region][$key]['#weight']) ? $build[$region][$key]['#weight'] : null;

        if ($key == 'polymer_content' || $key == 'polymer_page_title') { //TODO remove
          $build[$region][$key]['#cache']['contexts'] = array_merge($build[$region][$key]['#cache']['contexts'], ['route']);
        }

        $build[$region][$key] = [
          '#theme' => 'componentized_block',
          '#element_name' => Utilities::convertToElementName($key) . '-' . Utilities::hashedCurrentPath(),
          '#hash' => Utilities::hash(\Drupal::service('wcr.utilities')->createBlockID($build[$region][$key])),
          //'#weight' => $build[$region][$key]['#weight'],
          '#cache' => $build[$region][$key]['#cache'],
          '#original_cache' => $build[$region][$key]['#cache'],
          //TODO attachments
        ];
        if ($weight !== null) {
          $build[$region][$key]['#cache']['#weight'] == $weight;
        }
        $build[$region][$key]['#cache']['keys'][] = ['components_display', 'block', $key];

        $build[$region][$key]['#cache']['max-age'] = 0;
        //unset($build[$region][$key]['#cache']);
      }
     // $build[$region]['#sorted'] = false;
    }
    $blockList = new BlockList($build);
    //$blockList->setJsAssets(Utilities::getJsAssetsFromRenderArray($build));
    $debug = $blockList->toJson();
    if (!isset($build['#attached']['drupalSettings'])) {
      $build['#attached']['drupalSettings'] = [];
    }
    $build['#attached']['drupalSettings']['componentsBlockList'] = $debug;
    return $build;
  }

}
