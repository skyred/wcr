<?php
/**
 * @file
 * Contains Drupal\wcr\PageState
 */

namespace Drupal\wcr;

use Drupal\Core\Render\Element;
use Drupal\wcr\Service\Utilities;


class PageState {
  protected $url;
  protected $regions;
  protected $blocks;
  protected $title;
  protected $jsAssets;

  public function __construct($render_array) {
    foreach (Element::children($render_array) as $region) {
      $blocks = Element::children($render_array[$region]);
      foreach ($blocks as $key) {
        if ($key != 'components_display_region_wrapper') {
          $this->addBlock($render_array[$region][$key], $key, $region);
        }
      }
    }
    $this->title = '';
  }

  public function getUrl() {
    return $this->url;
  }

  public function toJson() {
    $result = [];

    foreach ($this->regions as $regionName => $blockList) {
      $result[$regionName] = [];
      foreach ($blockList as $key => $block) {
      //  $block = $blockList[$key];
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
      'title' => $this->title,
      'activeTheme' => \Drupal::theme()->getActiveTheme()->getName(),
      'hashSuffix' => Utilities::hashedCurrentPath(),
      'jsAssets' => $this->jsAssets,
    ]);
  }

  public function setJsAssets($attachment) {
    $this->jsAssets = $attachment;
  }

  public function setTitle($title) {
    $this->title = $title;
  }

  public function addBlock($block, $key, $region) {
    $this->blocks[$key] = $block;
    if (!isset($this->regions[$region])) {
      $this->regions[$region] = [];
    }
    $this->regions[$region][$key] = $block;
  }
}