<?php

namespace Drupal\wcr\Plugin\wcr\HTMLMainContentFormatter;

use Drupal\Core\Render\PageDisplayVariantSelectionEvent;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Render\RenderContext;
use Drupal\Core\Render\RenderEvents;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Display\ContextAwareVariantInterface;
use Drupal\Core\Display\PageVariantInterface;

trait BlockPreparationTrait {
  use PagePreparationTrait;
  protected $blocks;

  /**
   * Prepare all the blocks on the page.
   *
   * @param array $main_content
   * @param \Symfony\Component\HttpFoundation\Request $request
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   */
  protected function prepareBlocks(array $main_content, Request $request, RouteMatchInterface $route_match) {
    $this->page = $this->preparePage($main_content, $request, $route_match);

    // Iterate through all blocks.
    $regions = \Drupal::theme()->getActiveTheme()->getRegions();
    foreach ($regions as $region) {
      if (!empty($this->page[$region])) {
        // Non-empty region, iterate the blocks inside it.
        foreach ($this->page[$region] as $key => $child) {
          if (substr($key, 0, 1) != '#') {
            // Add `route` context to main content block
            if ($key == 'polymer_content' || $key == 'polymer_page_title') { //TODO remove
              $child['#cache']['contexts'] = array_merge($child['#cache']['contexts'], ['route']);
            }
            $this->blocks[$region . '/' . $key] = array(
              "id" => $region . '/' . $key,
              "render_array" => $child,
            );
          }
        }
      }
    }
    // Render each block.
    foreach ($this->blocks as &$block) {
      $block['markup'] = $this->getRenderer()->renderRoot($block['render_array']);

    }

    $this->getRenderer()->renderRoot($this->page);
    // Save the full assets of the page.
    $this->pageAttachments = $this->page['#attached'];
  }


}