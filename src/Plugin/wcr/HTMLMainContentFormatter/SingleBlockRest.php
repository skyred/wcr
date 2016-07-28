<?php
/**
 * @file
 * Contains \Drupal\wcr\Plugin\HTMLMainContentFormatter\SingleBlockRest.
 */

namespace Drupal\wcr\Plugin\wcr\HTMLMainContentFormatter;

use Drupal\Core\Ajax\AjaxResponse;
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
 *   id = "block_rest",
 *   name = @Translation("Single Block REST"),
 *   description = @Translation("Returns a block's markup and attachments in JSON format."),
 * )
 */
class SingleBlockRest extends HTMLMainContentFormatterBase {

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
        '#type' => 'bodyonly',
        'page' => $render_array,
      ];
      $html = $this->getRenderer()->mergeBubbleableMetadata($html, $render_array["#cache"]);
      // Add url to cache context, to prevent query arguments being ignored.
      $html['#cache']['contexts'] = Cache::mergeContexts($html['#cache']['contexts'], [ "url", "headers" ]);
      // url.query_args
      $html['#cache']['tags'][] = 'rendered';

      $head = $this->renderAttachments($this->pageAttachments);

      $this->getRenderer()->renderRoot($html);
      $response = new AjaxResponse([
        "content" => $html["#markup"],
        "attachments" => $head,
      ], 200, [
        'Content-Type' => 'application/json; charset=UTF-8',
        'Access-Control-Allow-Origin' => '*',
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