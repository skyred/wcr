<?php
/**
 * @file
 * Contains \Drupal\wcr\Plugin\HTMLMainContentFormatter\BlockListRest.
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
 *   id = "blocklist_rest",
 *   name = @Translation("BlockList REST"),
 *   command = "list_rest"
 * )
 */
class Polymer extends HTMLMainContentFormatterBase {

  public function response(array $main_content, Request $request, RouteMatchInterface $route_match) {
    //  Use a Symfony response object to have complete control over the response.
    $response = new Response();
    $response->setStatusCode(Response::HTTP_OK);
    $keys = array_keys($this->blocks);
    $response->headers->set('Content-Type', 'application/json');
    $response->setContent(\json_encode($keys));
    return $response;($debug);
    return $response;
  }
    
}