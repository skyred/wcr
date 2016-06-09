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


    list($page, $title) = $this->htmlRenderer->prepare($main_content, $request, $route_match);

    // URL parameters
    //$partials_required= $request->get("templates");


    //  We use a Symfony response object to have complete control over the response.
    $response = new Response();
    $response->setStatusCode(Response::HTTP_OK);

    $blocks = [];
    $regions = \Drupal::theme()->getActiveTheme()->getRegions();
    foreach ($regions as $region) {
      if (!empty($page[$region])) {
        foreach ($page[$region] as $key => $child) {
          if (substr($key,0,1) != '#') {
            $blocks[] = array (
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

    \kint($blocks);


    $debug_string = \json_encode($blocks);

    $response->headers->set('Content-Type', 'text/html');
    $response->setContent("OK");
    return $response;
  }



}
