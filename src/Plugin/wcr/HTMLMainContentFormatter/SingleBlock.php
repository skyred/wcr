<?php
/**
 * @file
 * Contains \Drupal\wcr\Plugin\HTMLMainContentFormatter\Polymer.
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
 *   id = "singleblock",
 *   name = @Translation("BlockList REST"),
 *   command = "list_rest"
 * )
 */
class SingleBlock extends HTMLMainContentFormatterBase {

  protected $renderer;

  function __construct() {
    parent::__construct();
    $this->renderer = \Drupal::service('renderer');
  }

  public function response(array $main_content, Request $request, RouteMatchInterface $route_match) {
    if (!empty($block_to_render)) {
      $render_array = $block_to_render['render_array'];

      // Merge the assets.
      $render_array = $this->renderer->mergeBubbleableMetadata($render_array, $this->pageAttachments);

      // Use a custom wrapper instead of `html` theme hook.
      $html = [
        '#type' => 'wcrhtml',
        'page' => $render_array,
        '#attached' => $this->pageAttachments,
      ];
      $html = $this->renderer->mergeBubbleableMetadata($html, $render_array["#cache"]);
      // Add url to cache context, to prevent query arguments being ignored.
      $html['#cache']['contexts'] = Cache::mergeContexts($html['#cache']['contexts'], [ "url", "headers" ]);
      // url.query_args
      $html['#cache']['tags'][] = 'rendered';

      $this->renderer->renderRoot($html);
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