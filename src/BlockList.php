<?php
/**
 * @file
 * Contains Drupal\wcr\BlockList
 */

namespace Drupal\wcr;


class BlockList {
  protected $url;
  protected $regions;
  protected $blocks;

  public function __construct() {
  }

  public function getUrl() {
    return $this->url;
  }

  public function toJson() {
    $result = [];

    foreach ($this->regions as $name => $list){
      $result[$name] = [];
      foreach ($list as $block) {
        $result[$name][] = $this->blocks[$block];
      }
    }

    return json_encode($result);
  }

  public function addBlock($block, $region) {
    $this->blocks[] = $block;
    if (!isset($this->regions[$region])) {
      $this->regions[$region] = [];
    }
    $this->regions[$region][] = $block;
  }
}