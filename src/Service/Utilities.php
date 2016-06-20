<?php

/**
 * @file
 * Contains \Drupal\wcr\Service\Utilities
 */

namespace Drupal\wcr\Service;

use Drupal\wcr\BlockList;

class Utilities {
  public function repeater(){
    
  }

  public static function getBlockName($block_id) {
    list($region, $name) = explode('/', $block_id);
    return $name;
  }

  public static function getElementName($block_id) {
    $name = self::getBlockName($block_id);
    $name = str_replace('_', '-', $name);
    return $name;
  }

  public static function replaceUnderscore($str) {
    return str_replace('_', '-', $str);
  }
}