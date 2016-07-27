<?php
/**
 * @file
 * Contains \Drupal\wcr\Plugin\HTMLMainContentFormatter\Polymer.
 */

namespace Drupal\wcr\Plugin\wcr\HTMLMainContentFormatter;

use Drupal\Core\Render\HtmlResponse;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\wcr\Plugin\HTMLMainContentFormatterBase;
use Drupal\wcr\Plugin\wcr\HTMLMainContentFormatter\PagePreparationTrait;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\PreconditionFailedHttpException;
/**
 * Returns a list of all blocks on the page.
 *
 * @HTMLMainContentFormatter(
 *   id = "polymer",
 *   name = @Translation("Polymer Element"),
 * )
 */
class Polymer extends HTMLMainContentFormatterBase {
  use PagePreparationTrait;
  use BlockPreparationTrait;

  protected $elementName;

  public function handle(array $main_content, Request $request, RouteMatchInterface $route_match) {
    // Get parameters.
    $elementName = $request->get("_wcr_element_name");
    $block_requested = $request->get("_wcr_block");

    $this->prepareBlocks($main_content, $request, $route_match);
    return $this->generateResponse($this->blocks[$block_requested], $elementName);
  }

  protected function generateResponse($block_to_render, $elementName) {
    if (!empty($block_to_render)) {
      $render_array = $block_to_render['render_array'];

      // Merge the assets.
      $render_array = $this->getRenderer()->mergeBubbleableMetadata($render_array, $this->pageAttachments);

      // Use a custom wrapper instead of `html` theme hook.
      $html = [
        '#type' => 'polymer',
        'page' => $render_array,
        '#element_name' => $this->elementName,
        '#attached' => $this->pageAttachments,
      ];
      $html = $this->getRenderer()->mergeBubbleableMetadata($html, $render_array["#cache"]);
      // Add url to cache context, to prevent query arguments being ignored.
      $html['#cache']['contexts'] = Cache::mergeContexts($html['#cache']['contexts'], [ "url", "headers" ]);
      // url.query_args
      $html['#cache']['tags'][] = 'rendered';

      $this->getRenderer()->renderRoot($html);
      $response = new HtmlResponse($html, 200, [
        'Content-Type' => 'text/html; charset=UTF-8',
      ]);
      return $response;
    }
    else {
      $response = new Response();
      $response->setContent("Block not found.");
      $response->setStatusCode(Response::HTTP_NOT_FOUND);
      return $response;
    }
  }
}