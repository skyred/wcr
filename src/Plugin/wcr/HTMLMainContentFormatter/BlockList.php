<?php
/**
 * @file
 * Contains \Drupal\wcr\Plugin\HTMLMainContentFormatter\BlockList.
 */

namespace Drupal\wcr\Plugin\HTMLMainContentFormatter;

use Drupal\wcr\HTMLMainContentFormatterBase;

/**
 * Returns a list of all blocks on the page.
 *
 * @HTMLMainContentFormatter(
 *   id = "blocklist",
 *   name = @Translation("BlockList"),
 *   command = "list"
 * )
 */
class BlockList extends HTMLMainContentFormatterBase {
    // Use a Symfony response object to have complete control over the response.
    $response = new Response();
    $response->setStatusCode(Response::HTTP_OK);

    $debug = "";
    $keys = array_keys($this->blocks);
    foreach ($keys as $key) {
      $debug .= $key;
      $debug .= ' ';
      $debug .= Utilities::hash(\Drupal::service('wcr.utilities')->createBlockID($this->blocks[$key]['render_array']));
      $tmp = $this->blocks[$key]['render_array'];
      $this->renderer->renderRoot($tmp);
      $region_metadata = BubbleableMetadata::createFromRenderArray($tmp);
      $debug .= ' ' . \Drupal::service('wcr.utilities')->createBlockID($tmp);
      $debug .= '<br /> ';
    }

    $response->headers->set('Content-Type', 'text/html');
    $response->setContent($debug);
    return $response;
}
