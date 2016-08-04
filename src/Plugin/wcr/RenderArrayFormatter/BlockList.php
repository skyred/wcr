<?php
/**
 * @file
 * Contains \Drupal\wcr\Plugin\RenderArrayFormatter\BlockList.
 */

namespace Drupal\wcr\Plugin\wcr\RenderArrayFormatter;

use Drupal\wcr\PagePreparationTrait;
use Drupal\wcr\Plugin\RenderArrayFormatterBase;
use Drupal\wcr\Plugin\wcr\RenderArrayFormatter\BlockPreparationTrait;
use Symfony\Component\DependencyInjection\Exception\ParameterNotFoundException;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\wcr\Service\Utilities;
use Drupal\Core\Render\BubbleableMetadata;

/**
 * Returns a list of all blocks on the page.
 *
 * @RenderArrayFormatter(
 *   id = "list",
 *   name = @Translation("BlockList"),
 *   description = @Translation("Returns a list of block on the page."),
 * )
 */
class BlockList extends RenderArrayFormatterBase {
  use BlockPreparationTrait, PagePreparationTrait;

  protected $blocks;

  protected $renderer;

  public function __construct(array $configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->renderer = \Drupal::service("renderer");
  }

  protected function doGenerateResponse() {
    // Use a Symfony response object to have complete control over the response.
    $response = new Response();
    $response->setStatusCode(Response::HTTP_OK);

    $debug = '<table>';

    $debug .= '<tr>'
             .'<td>Region</td>'
             .'<td>BlockName</td>'
             .'<td>hash</td>'
             .'<td>blockID</td>'
             .'</tr>';
    $keys = array_keys($this->blocks);
    foreach ($keys as $key) {
      $debug .= '<tr>';
      $debug .= '<td>' . $this->blocks[$key]['region'] . '</td>';
      $debug .= '<td>' . $key . '</td>';
      $debug .= '<td>' . Utilities::hash($this->wcrUtilities->createBlockID($this->blocks[$key]['render_array'])) . '</td>';
      $tmp = $this->blocks[$key]['render_array'];
      $this->renderer->renderRoot($tmp);
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
  public function generateResponse(array $page, array $options = []) {
    if (!isset($options['request'])) {
      throw new ParameterNotFoundException('request');
    }

    if (!isset($options['route_match'])) {
      throw new ParameterNotFoundException('route_match');
    }

    $page = $this->preparePage($page, $options['request'], $options['route_match']);

    $this->blocks = $this->getBlocks($page);
    return $this->doGenerateResponse();
  }


}
