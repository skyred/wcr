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
 *   id = "spf",
 *   name = @Translation("Structured Page Fragments"),
 *   description = @Translation("Returns a block's markup and attachments in JSON format."),
 * )
 */
class SPF extends HTMLMainContentFormatterBase {

  use PagePreparationTrait;


  public function handle(array $main_content, Request $request, RouteMatchInterface $route_match) {
    // Get parameters.
    // Render response.
    $this->page = $this->preparePage($main_content, $request, $route_match);
    $this->pageAttachments = $this->prepareAttachments($this->page);

    return $this->generateResponse();
  }

  public function generateResponse() {

    $render_array = $this->page;

    // Merge the assets.
    $render_array = $this->getRenderer()->mergeBubbleableMetadata($render_array, $this->pageAttachments);


    $attachments = $this->renderAttachments($this->pageAttachments);

    $this->getRenderer()->renderRoot($render_array);

    $response = new Response();
    $response->setStatusCode(Response::HTTP_OK);
    $response->headers->set('Content-Type', 'application/json; charset=UTF-8');
    $response->headers->set('Access-Control-Allow-Origin', '*');

    $response->setContent(\json_encode([
      "body" => $render_array["#markup"],
      "head" => $attachments['head'] . $attachments['scripts'],
      "foot" => $attachments['scripts_bottom'],
      "title" => 'test title',
    ]));

    return $response;

  }
    
}