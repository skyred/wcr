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
    $name = self::convertToElementName($name);
    return $name;
  }

  public static function convertToElementName($str) {
    $tmp = str_replace('_', '-', $str);
    if (strpos($tmp, '-') === FALSE) {
      $tmp = 'x-' . $tmp;
    }
    return $tmp;
  }
}