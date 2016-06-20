<?php

/**
 * @file
 * Contains \Drupal\wcr\Plugin\DisplayVariant\ComponentsDisplayVariant.
 */

namespace Drupal\wcr\Plugin\DisplayVariant;
use Drupal\block\Plugin\DisplayVariant\BlockPageVariant;

use Drupal\Component\Uuid\UuidInterface;
use Drupal\Component\Utility\Html;
use Drupal\Core\Block\BlockManager;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Condition\ConditionManager;
use Drupal\Core\Display\PageVariantInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\Context\ContextHandlerInterface;
use Drupal\Core\Plugin\ContextAwarePluginInterface;
use Drupal\Core\Render\Element;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Utility\Token;
use Symfony\Component\DependencyInjection\ContainerInterface;

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

    foreach (Element::children($build) as $region => $blocks) {
      $build[$region]['components_page_region_wrapper'] = ['#markup' => '<div data-components-page-region></div>'];

      foreach ($blocks as $key => $block) {
        $build[$region][$key] = [
          '#theme' => 'componentized_block',
          '#element_name' => $region . '/' . $key,
          '#weight' => $build[$region][$key]['#weight'],
          '#cache' => $build[$region][$key]['#cache'],
        ];
        $build[$region][$key]['#cache']['keys'][] = ['components_display', $this->id(), 'block', $key];
      }
    }

    return $build;
  }

}
