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

  /**
   * WebComponentRenderer constructor.
   * @param MainContentRendererInterface $html_renderer
   */
  public function __construct(MainContentRendererInterface $html_renderer) {
    $this->htmlRenderer = $html_renderer;
    // Modified version of the core "renderer" service
    $this->renderer = \Drupal::service('renderer');
  }

  /**
   * {@inheritdoc}
   */
  public function renderResponse(array $main_content, Request $request, RouteMatchInterface $route_match) {
    // URL parameter
    $block_requested = $request->get("block");

    list($page, $title) = $this->htmlRenderer->prepare($main_content, $request, $route_match);

    $page_attachments = $page['#attached'];

    $blocks = [];
    $regions = \Drupal::theme()->getActiveTheme()->getRegions();
    foreach ($regions as $region) {
      if (!empty($page[$region])) {
        foreach ($page[$region] as $key => $child) {
          if (substr($key,0,1) != '#') {
            $blocks[$region .'/' .$key] = array (
              "id" => $region .'/' .$key,
              "render_array" => $child,
            );
          }
        }

      }
    }
    foreach ($blocks as &$block) {
      $block['markup'] = $this->renderer->renderRoot($block['render_array']);
    }

    $this->renderer->renderRoot($page);
    $page_attachments = $page['#attached'];

    if (empty($block_requested)) {
      // List Mode

      //  We use a Symfony response object to have complete control over the response.
      $response = new Response();
      $response->setStatusCode(Response::HTTP_OK);

      $debug = "";
      $keys = array_keys($blocks);
      foreach ($keys as $key) {
        $debug = $debug. $key . '<br /> ';
      }

      $response->headers->set('Content-Type', 'text/html');
      $response->setContent($debug);
      return $response;
    } else {
      //Render Mode
      $block_to_render = $blocks[$block_requested];
      if (!empty($block_to_render)) {
        $render_array = $block_to_render['render_array'];
        $render_array = $this->renderer->mergeBubbleableMetadata($render_array, $page_attachments);
        $html = [
          '#type' => 'wcrhtml',
          'page' => $render_array,
          '#attached'=> $page_attachments,
        ];
        $html = $this->renderer->mergeBubbleableMetadata($html, $render_array["#cache"]);
        $html['#cache']['contexts'] = Cache::mergeContexts($html['#cache']['contexts'], [ "url" ]); //url.query_args

        $html ['#cache']['tags'][] = 'rendered';
        $this->renderer->renderRoot($html);
        $response = new HtmlResponse($html, 200, [
          'Content-Type' => 'text/html; charset=UTF-8',
        ]);
        return $response;
      }
      else {
        $response = new Response();
        $response->setStatusCode(Response::HTTP_NOT_FOUND);
        return $response;
      }
    }


  }



}
