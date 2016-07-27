<?php
/**
 * @file
 * Contains \Drupal\wcr\Plugin\HTMLMainContentFormatter\BlockList.
 */

namespace Drupal\wcr\Plugin\wcr\HTMLMainContentFormatter;

use Drupal\wcr\Plugin\HTMLMainContentFormatterBase;
use Drupal\wcr\Plugin\wcr\HTMLMainContentFormatter\PagePreparationTrait;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\PreconditionFailedHttpException;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\wcr\Service\Utilities;
use Drupal\Core\Render\BubbleableMetadata;

/**
 * Returns a list of all blocks on the page.
 *
 * @HTMLMainContentFormatter(
 *   id = "list",
 *   name = @Translation("BlockList"),
 * )
 */
class BlockList extends HTMLMainContentFormatterBase {
  use PagePreparationTrait;
  use BlockPreparationTrait;

  private function generateResponse() {
    // Use a Symfony response object to have complete control over the response.
    $response = new Response();
    $response->setStatusCode(Response::HTTP_OK);

    $debug = "";
    $keys = array_keys($this->blocks);
    foreach ($keys as $key) {
      $debug .= $key;
      $debug .= ' ';
      $debug .= Utilities::hash($this->wcrUtilities->createBlockID($this->blocks[$key]['render_array']));
      $tmp = $this->blocks[$key]['render_array'];
      $this->getRenderer()->renderRoot($tmp);
      $region_metadata = BubbleableMetadata::createFromRenderArray($tmp);
      $debug .= ' ' . $this->wcrUtilities->createBlockID($tmp);
      $debug .= '<br /> ';
    }

    $response->headers->set('Content-Type', 'text/html');
    $response->setContent($debug);
    return $response;
  }

  /**
   * @inheritdoc
   */
  public function handle(array $main_content, Request $request, RouteMatchInterface $route_match) {
    $this->prepareBlocks($main_content, $request, $route_match);

    return $this->generateResponse();
  }


}
