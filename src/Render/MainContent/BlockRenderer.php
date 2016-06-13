<?php

/**
 * @file
 * Contains \Drupal\wcr\Render\MainContent\PartialRenderer.
 */

namespace Drupal\wcr\Render\MainContent;

use Drupal\Component\Utility\Crypt;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\InsertCommand;
use Drupal\Core\Render\AttachmentsResponseProcessorInterface;

use Drupal\Core\Render\BubbleableMetadata;
use Drupal\Core\Render\HtmlResponse;
use Drupal\Core\Render\MainContent\MainContentRendererInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Cache\Cache;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\PreconditionFailedHttpException;

/**
 * Default main content renderer for page partials.
 *
 * Currently it only prints debug info.
 */
class BlockRenderer implements MainContentRendererInterface {


  /**
   * The HTML response attachments processor service.
   *
   * @var \Drupal\Core\Render\AttachmentsResponseProcessorInterface
   */
  protected $htmlRenderer;

  protected $blocks;
  protected $page_attachments;
  protected $page;

  /**
   * WebComponentRenderer constructor.
   * @param MainContentRendererInterface $html_renderer
   */
  public function __construct(MainContentRendererInterface $html_renderer) {
    $this->htmlRenderer = $html_renderer;
    $this->renderer = \Drupal::service('renderer');

    $this->blocks = [];
  }

  protected function prepare(array $main_content, Request $request, RouteMatchInterface $route_match) {
    // TODO: remove reliance on HTMLRenderer.
    list($this->page, $title) = $this->htmlRenderer->prepare($main_content, $request, $route_match);

    // Iterate through all blocks.
    $regions = \Drupal::theme()->getActiveTheme()->getRegions();
    foreach ($regions as $region) {
      if (!empty($this->page[$region])) {
        // Non-empty region, iterate the blocks inside it.
        foreach ($this->page[$region] as $key => $child) {
          if (substr($key,0,1) != '#') {
            $this->blocks[$region .'/' .$key] = array (
              "id" => $region .'/' .$key,
              "render_array" => $child,
            );
          }
        }

      }
    }
    // Render each block
    foreach ($this->blocks as &$block) {
      $block['markup'] = $this->renderer->renderRoot($block['render_array']);
    }

    $this->renderer->renderRoot($this->page);
    // Save the full assets of the page.
    $this->page_attachments = $this->page['#attached'];
  }

  /**
   * {@inheritdoc}
   */
  public function renderResponse(array $main_content, Request $request, RouteMatchInterface $route_match) {
    // Process URL parameters.
    $block_requested = $request->get("block");
    $is_REST = $request->getContentType() == "application/json";
    // Prepare.
    $this->prepare($main_content, $request, $route_match);

    if (empty($block_requested)) {
      // List Mode
      return $this->renderBlockList();
    } else {
      // Render Mode
      if ($is_REST) {
        return $this->renderBlockREST($this->blocks[$block_requested]);
      } else {
        return $this->renderBlock($this->blocks[$block_requested]);
      }
    }
  }

  protected function renderBlockList() {
    //  Use a Symfony response object to have complete control over the response.
    $response = new Response();
    $response->setStatusCode(Response::HTTP_OK);

    $debug = "";
    $keys = array_keys($this->blocks);
    foreach ($keys as $key) {
      $debug = $debug. $key . '<br /> ';
    }

    $response->headers->set('Content-Type', 'text/html');
    $response->setContent($debug);
    return $response;
  }

  protected function renderBlock($block_to_render) {
    if (!empty($block_to_render)) {
      $render_array = $block_to_render['render_array'];

      // Merge the assets.
      $render_array = $this->renderer->mergeBubbleableMetadata($render_array, $this->page_attachments);

      // Use a custom wrapper instead of `html` theme hook.
      $html = [
        '#type' => 'wcrhtml',
        'page' => $render_array,
        '#attached'=> $this->page_attachments,
      ];
      $html = $this->renderer->mergeBubbleableMetadata($html, $render_array["#cache"]);
      // Add url to cache context, to prevent query arguments being ignored.
      $html['#cache']['contexts'] = Cache::mergeContexts($html['#cache']['contexts'], [ "url" ]); //url.query_args

      $html ['#cache']['tags'][] = 'rendered';

      //
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

  protected function renderBlockREST($block_to_render) {
    if (!empty($block_to_render)) {
      $render_array = $block_to_render['render_array'];

      // Merge the assets.
      $render_array = $this->renderer->mergeBubbleableMetadata($render_array, $this->page_attachments);

      // Use a custom wrapper instead of `html` theme hook.
      $html = [
        '#type' => 'wcrhtml',
        'page' => $render_array,
        '#attached'=> $this->page_attachments,
      ];
      $html = $this->renderer->mergeBubbleableMetadata($html, $render_array["#cache"]);
      // Add url to cache context, to prevent query arguments being ignored.
      $html['#cache']['contexts'] = Cache::mergeContexts($html['#cache']['contexts'], [ "url" ]); //url.query_args

      $html ['#cache']['tags'][] = 'rendered';

      //
      $this->renderer->renderRoot($html);
      $response = new AjaxResponse($html, 200, [
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
