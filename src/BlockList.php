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

    foreach ($this->regions as $name => $list){
      $result[$name] = [];
      foreach ($list as $block) {
        $result[$name][] = $block;
      }
    }

    return json_encode([
      'regions' => $this->regions,
      'hash' => Utilities::hashedCurrentPath(),
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