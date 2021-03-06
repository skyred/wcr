<?php
/**
 * @file
 * Contains \Drupal\wcr\Plugin\RenderArrayFormatter\Polymer.
 */

namespace Drupal\wcr\Plugin\wcr\RenderArrayFormatter;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Render\HtmlResponse;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\wcr\Plugin\RenderArrayFormatterBase;
use Drupal\wcr\PagePreparationTrait;
use Drupal\wcr\Service\Utilities;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\PreconditionFailedHttpException;
/**
 * Returns a list of all blocks on the page.
 *
 * @RenderArrayFormatter(
 *   id = "polymer_bare",
 *   name = @Translation("Polymer Element Bare"),
 *   description = @Translation("Returns a block wrappered as Polymer element. (Automatic naming)"),
 * )
 */
class PolymerBare extends Polymer {
  use PagePreparationTrait;
  use BlockPreparationTrait;

  protected function doGenerateResponse($block_to_render, $elementName = '') {
    if (!empty($block_to_render)) {
      $render_array = $block_to_render['render_array'];
      $name = Utilities::getElementName($block_to_render["id"]);
      $cacheID = \Drupal::service('wcr.utilities')->createBlockID($render_array);
      // Use a custom wrapper instead of `html` theme hook.
      $html = [
        '#type' => 'polymerbare',
        'page' => $render_array,
        '#element_name' => $name . '-' . Utilities::hashedCurrentPath(),
        //'#attached' => $this->pageAttachments,  //@todo: recheck
      ];
      $html = $this->getRenderer()->mergeBubbleableMetadata($html, $render_array["#cache"]);
      // Add url to cache context, to prevent query arguments being ignored.
      $html['#cache']['contexts'] = Cache::mergeContexts($html['#cache']['contexts'], [ "url", "headers" ]);
      // url.query_args
      $html['#cache']['tags'][] = 'rendered';

      if ($name == 'x-messages') {
        $html = [
          "#markup" => '',
        ];
      }
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