<?php
/**
 * @file
 * Contains Drupal\wcr\BlockList
 */

namespace Drupal\wcr;

use Drupal\Core\Render\Element;
use Drupal\wcr\Service\Utilities;


class BlockList {
  protected $url;
  protected $regions;
  protected $blocks;

  public function __construct($render_array) {
    foreach (Element::children($render_array) as $region) {
      $blocks = Element::children($render_array[$region]);
      foreach ($blocks as $key) {
        if ($key != 'components_display_region_wrapper') {
          $this->addBlock($render_array[$region][$key], $key, $region);
        }
      }
    }
  }

  public function getUrl() {
    return $this->url;
  }

  public function toJson() {
    $result = [];

    foreach ($this->regions as $regionName => $blockList){
      $result[$regionName] = [];
      foreach ($blockList as $key => $block) {
        $result[$regionName][$key] = [
          'element_name' => (isset($block['#element_name']))? $block['#element_name'] : (Utilities::convertToElementName($key) . '-' . Utilities::hashedCurrentPath()),
          'hash' => (isset($block['#hash'])) ? $block['#hash'] : Utilities::hash(\Drupal::service('wcr.utilities')->createBlockID($block)),
          'region' => $regionName,
          'block' => $key,
        ];
        if (isset($block['#original_cache'])) {
          $result[$regionName][$key]['cache'] = $block['#original_cache'];
        }
        else {
          $result[$regionName][$key]['cache'] = $block['#cache'];
        }
      }
    }

    return json_encode([
      'regions' => $result,
      'hashSuffix' => Utilities::hashedCurrentPath(),
    ]);
  }

  public function addBlock($block, $key, $region) {
    $this->blocks[$key] = $block;
    if (!isset($this->regions[$region])) {
      $this->regions[$region] = [];
    }
    $this->regions[$region][$key] = $block;
  }
}