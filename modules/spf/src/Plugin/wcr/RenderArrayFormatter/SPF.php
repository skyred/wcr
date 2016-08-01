<?php
/**
 * @file
 * Contains \Drupal\wcr\Plugin\RenderArrayFormatter\SingleBlockRest.
 */

namespace Drupal\spf\Plugin\wcr\RenderArrayFormatter;

use Drupal\Core\Ajax\AjaxResponse;
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
 *   id = "spf",
 *   name = @Translation("Structured Page Fragments"),
 *   description = @Translation("Returns a block's markup and attachments in JSON format."),
 * )
 */
class SPF extends RenderArrayFormatterBase {

  use PagePreparationTrait;


  public function generateResponse(array $renderArray, array $options = []) {
    if (!isset($options['request'])) {
      throw new ParameterNotFoundException('request');
    }

    if (!isset($options['route_match'])) {
      throw new ParameterNotFoundException('route_match');
    }
    // Render response.
    $this->page = $this->preparePage($renderArray, $options['request'], $options['route_match']);
    $this->pageAttachments = $this->prepareAttachments($this->page);

    return $this->doGenerateResponse();
  }

  public function doGenerateResponse() {

    $render_array = $this->page;

    // Merge the assets.
    $render_array = $this->getRenderer()->mergeBubbleableMetadata($render_array, $this->pageAttachments);


    $attachments = $this->renderAttachments($this->pageAttachments);

    //$this->getRenderer()->renderRoot($render_array);

    $body = [];
    $regions = \Drupal::theme()->getActiveTheme()->getRegions();
    foreach ($regions as $region) {
      $body['spf-' . $region] = $this->getRenderer()->renderRoot($render_array[$region]);;
    }

    $response = new Response();
    $response->setStatusCode(Response::HTTP_OK);
    $response->headers->set('Content-Type', 'application/json; charset=UTF-8');
    $response->headers->set('Access-Control-Allow-Origin', '*');

    $response->setContent(\json_encode([
      "body" => $body,
      "head" => $attachments['head'] . $attachments['scripts'],
      "foot" => $attachments['scripts_bottom'],
      "title" => 'test title',
    ]));

    return $response;

  }
    
}