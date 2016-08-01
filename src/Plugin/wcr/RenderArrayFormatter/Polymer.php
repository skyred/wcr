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
use Symfony\Component\DependencyInjection\Exception\ParameterNotFoundException;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\PreconditionFailedHttpException;
/**
 * Returns a list of all blocks on the page.
 *
 * @RenderArrayFormatter(
 *   id = "polymer",
 *   name = @Translation("Polymer Element"),
 *   description = @Translation("Returns a block wrappered as Polymer element."),
 * )
 */
class Polymer extends RenderArrayFormatterBase {
  use PagePreparationTrait;
  use BlockPreparationTrait;

  protected $elementName;
  protected $blocks;

  public function generateResponse(array $main_content, array $options = []) {
    if (!isset($options['request'])) {
      throw new ParameterNotFoundException('request');
    }

    if (!isset($options['route_match'])) {
      throw new ParameterNotFoundException('route_match');
    }

    // Get parameters.
    $elementName = $options['request']->get("_wcr_element_name");
    $block_requested = $options['request']->get("_wcr_block");

    $this->page = $this->preparePage($main_content, $options['request'], $options['route_match']);
    $this->blocks = $this->getBlocks($this->page);
    $this->pageAttachments = $this->prepareAttachments($this->page);

    return $this->doGenerateResponse($this->blocks[$block_requested], $elementName);
  }

  protected function doGenerateResponse($block_to_render, $elementName) {
    if (!empty($block_to_render)) {
      $render_array = $block_to_render['render_array'];

      // Merge the assets.
      $render_array = $this->getRenderer()->mergeBubbleableMetadata($render_array, $this->pageAttachments);

      // Use a custom wrapper instead of `html` theme hook.
      $html = [
        '#type' => 'polymer',
        'page' => $render_array,
        '#element_name' => $elementName,
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