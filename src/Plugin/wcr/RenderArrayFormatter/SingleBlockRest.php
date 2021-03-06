<?php
/**
 * @file
 * Contains \Drupal\wcr\Plugin\RenderArrayFormatter\SingleBlockRest.
 */

namespace Drupal\wcr\Plugin\wcr\RenderArrayFormatter;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Render\HtmlResponse;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\wcr\Plugin\RenderArrayFormatterBase;
use Drupal\wcr\PagePreparationTrait;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\PreconditionFailedHttpException;
/**
 * Returns a list of all blocks on the page.
 *
 * @RenderArrayFormatter(
 *   id = "block_rest",
 *   name = @Translation("Single Block REST"),
 *   description = @Translation("Returns a block's markup and attachments in JSON format."),
 * )
 */
class SingleBlockRest extends SingleBlock  {

  use PagePreparationTrait;
  use BlockPreparationTrait;

  protected function doGenerateResponse($block_to_render) {
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