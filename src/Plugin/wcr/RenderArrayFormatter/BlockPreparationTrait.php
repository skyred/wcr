<?php

namespace Drupal\wcr\Plugin\wcr\RenderArrayFormatter;

use Drupal\Core\Render\PageDisplayVariantSelectionEvent;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Render\RenderContext;
use Drupal\Core\Render\RenderEvents;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Display\ContextAwareVariantInterface;
use Drupal\Core\Display\PageVariantInterface;

trait BlockPreparationTrait {

  /**
   * Prepare all the blocks on the page.
   *
   * @param $preparedPage
   * @return array
   */
  protected function getBlocks($preparedPage) {
    // Iterate through all blocks.
    $regions = \Drupal::theme()->getActiveTheme()->getRegions();
    $blocks = [];

    foreach ($regions as $region) {
      if (!empty($preparedPage[$region])) {
        // Non-empty region, iterate the blocks inside it.
        foreach ($preparedPage[$region] as $key => $child) {
          if (substr($key, 0, 1) != '#') {
            // Add `route` context to main content block
            if ($key == 'polymer_content' || $key == 'polymer_page_title') { //TODO remove
              $child['#cache']['contexts'] = array_merge($child['#cache']['contexts'], ['route']);
            }
            $blocks[$region . '/' . $key] = array(
              "id" => $region . '/' . $key,
              "render_array" => $child,
            );
          }
        }
      }
    }

    return $blocks;

  }


}