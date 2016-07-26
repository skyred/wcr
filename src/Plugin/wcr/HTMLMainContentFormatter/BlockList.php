<?php
/**
 * @file
 * Contains \Drupal\wcr\Plugin\HTMLMainContentFormatter\BlockList.
 */

namespace Drupal\wcr\Plugin\HTMLMainContentFormatter;

use Drupal\wcr\HTMLMainContentFormatterBase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\PreconditionFailedHttpException;
/**
 * Returns a list of all blocks on the page.
 *
 * @HTMLMainContentFormatter(
 *   id = "blocklist",
 *   name = @Translation("BlockList"),
 *   command = "list"
 * )
 */
class BlockList extends HTMLMainContentFormatterBase {
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
      $block['markup'] = $this->renderer->renderRoot($block['render_array']);
    }

    $this->renderer->renderRoot($this->page);
    // Save the full assets of the page.
    $this->pageAttachments = $this->page['#attached'];
  }
  
  /**
   * @inheritdoc
   */
  public function handle(array $main_content, Request $request, RouteMatchInterface $route_match) {
    $this->prepareBlocks();
    
    return $this->generateResponse();
  }

  private function generateResponse() {
    // Use a Symfony response object to have complete control over the response.
    $response = new Response();
    $response->setStatusCode(Response::HTTP_OK);

    $debug = "";
    $keys = array_keys($this->blocks);
    foreach ($keys as $key) {
      $debug .= $key;
      $debug .= ' ';
      $debug .= Utilities::hash($this->wcrUtilities->createBlockID($this->blocks[$key]['render_array']));
      $tmp = $this->blocks[$key]['render_array'];
      $this->renderer->renderRoot($tmp);
      $region_metadata = BubbleableMetadata::createFromRenderArray($tmp);
      $debug .= ' ' . $this->wcrUtilities->createBlockID($tmp);
      $debug .= '<br /> ';
    }

    $response->headers->set('Content-Type', 'text/html');
    $response->setContent($debug);
    return $response;
  }
    
}
