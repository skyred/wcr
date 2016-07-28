<?php
/**
 * @file
 * Contains \Drupal\wcr\Plugin\HTMLMainContentFormatter\BlockList.
 */

namespace Drupal\wcr\Plugin\wcr\HTMLMainContentFormatter;

use Drupal\wcr\Plugin\HTMLMainContentFormatterBase;
use Drupal\wcr\Plugin\wcr\HTMLMainContentFormatter\PagePreparationTrait;
use Drupal\wcr\Plugin\wcr\HTMLMainContentFormatter\BlockPreparationTrait;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\wcr\Service\Utilities;
use Drupal\Core\Render\BubbleableMetadata;

/**
 * Returns a list of all blocks on the page.
 *
 * @HTMLMainContentFormatter(
 *   id = "list",
 *   name = @Translation("BlockList"),
 *   description = @Translation("Returns a list of block on the page."),
 * )
 */
class BlockList extends HTMLMainContentFormatterBase {
  use PagePreparationTrait, BlockPreparationTrait;

  protected $blocks;

  private function generateResponse() {
    // Use a Symfony response object to have complete control over the response.
    $response = new Response();
    $response->setStatusCode(Response::HTTP_OK);

    $debug = '<table>';

    $debug .= '<tr>'
             .'<td>key</td>'
             .'<td>hash</td>'
             .'<td>blockID</td>'
             .'</tr>';
    $keys = array_keys($this->blocks);
    foreach ($keys as $key) {
      $debug .= '<tr>';
      $debug .= '<td>' . $key . '</td>';
      $debug .= '<td>' . Utilities::hash($this->wcrUtilities->createBlockID($this->blocks[$key]['render_array'])) . '</td>';
      $tmp = $this->blocks[$key]['render_array'];
      $this->getRenderer()->renderRoot($tmp);
      $region_metadata = BubbleableMetadata::createFromRenderArray($tmp);
      $debug .= '<td>' . $this->wcrUtilities->createBlockID($tmp) . '</td>';
      $debug .= '</td> ';
    }
    $debug .= '</table>';

    $response->headers->set('Content-Type', 'text/html');
    $response->setContent($debug);
    return $response;
  }

  /**
   * @inheritdoc
   */
  public function handle(array $main_content, Request $request, RouteMatchInterface $route_match) {
    $this->page = $this->preparePage($main_content, $request, $route_match);
    $this->blocks = $this->getBlocks($this->page);

    return $this->generateResponse();
  }


}
