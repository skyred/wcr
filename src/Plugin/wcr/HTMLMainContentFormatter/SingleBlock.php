<?php
/**
 * @file
 * Contains \Drupal\wcr\Plugin\HTMLMainContentFormatter\Polymer.
 */

namespace Drupal\wcr\Plugin\wcr\HTMLMainContentFormatter;

use Drupal\Core\Cache\Cache;
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
 *   id = "singleblock",
 *   name = @Translation("Single Block"),
 *   description = @Translation("Returns an HTML page with only one block."),
 * )
 */
class SingleBlock extends HTMLMainContentFormatterBase {

  use PagePreparationTrait;
  use BlockPreparationTrait;

  protected $blocks;

  public function handle(array $main_content, Request $request, RouteMatchInterface $route_match) {
    // Get parameters.
    $block_requested = $request->get("_wcr_block");
    // Render response.
    $this->page = $this->preparePage($main_content, $request, $route_match);
    $this->blocks = $this->getBlocks($this->page);
    $this->pageAttachments = $this->prepareAttachments($this->page);

    return $this->generateResponse($this->blocks[$block_requested]);
  }

  public function generateResponse($block_to_render) {
    if (!empty($block_to_render)) {
      $render_array = $block_to_render['render_array'];

      // Merge the assets.
      $render_array = $this->getRenderer()->mergeBubbleableMetadata($render_array, $this->pageAttachments);

      // Use a custom wrapper instead of `html` theme hook.
      $html = [
        '#type' => 'wcrhtml',
        'page' => $render_array,
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