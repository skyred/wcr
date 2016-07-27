<?php
/**
 * @file
 * Contains \Drupal\wcr\Plugin\HTMLMainContentFormatter\BlockListRest.
 */

namespace Drupal\wcr\Plugin\wcr\HTMLMainContentFormatter;

use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\wcr\Plugin\HTMLMainContentFormatterBase;
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
 *   description = @Translation("Returns a list of block on the page in JSON format."),
 * )
 */
class BlockListRest extends BlockList {

  public function handle(array $main_content, Request $request, RouteMatchInterface $route_match) {
    $this->page = $this->preparePage($main_content, $request, $route_match);
    $this->blocks = $this->getBlocks($this->page);

    $response = new Response();
    $response->setStatusCode(Response::HTTP_OK);
    $keys = array_keys($this->blocks);
    $response->headers->set('Content-Type', 'application/json');
    $response->setContent(\json_encode($keys));
    return $response;
  }
    
}