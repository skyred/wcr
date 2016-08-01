<?php
/**
 * @file
 * Contains \Drupal\wcr\Plugin\RenderArrayFormatter\BlockListRest.
 */

namespace Drupal\wcr\Plugin\wcr\RenderArrayFormatter;

use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\wcr\Plugin\RenderArrayFormatterBase;
use Symfony\Component\DependencyInjection\Exception\ParameterNotFoundException;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\PreconditionFailedHttpException;
/**
 * Returns a list of all blocks on the page.
 *
 * @RenderArrayFormatter(
 *   id = "blocklist_rest",
 *   name = @Translation("BlockList REST"),
 *   description = @Translation("Returns a list of block on the page in JSON format."),
 * )
 */
class BlockListRest extends BlockList {

  public function generateResponse(array $page, array $options = []) {
    if (!isset($options['request'])) {
      throw new ParameterNotFoundException('request');
    }

    if (!isset($options['route_match'])) {
      throw new ParameterNotFoundException('route_match');
    }

    $page = $this->preparePage($page, $options['request'], $options['route_match']);

    $response = new Response();
    $response->setStatusCode(Response::HTTP_OK);
    $keys = array_keys($this->blocks);
    $response->headers->set('Content-Type', 'application/json');
    $response->setContent(\json_encode($keys));
    return $response;
  }

}