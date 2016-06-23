<?php

/**
 * @file
 * Contains \Drupal\wcr\Service\Utilities
 */

namespace Drupal\wcr\Service;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Cache\Context\CacheContextsManager;
use Drupal\Core\Url;
use Drupal\wcr\BlockList;

class Utilities {

  /**
   * @var \Drupal\wcr\Service\CacheContextsManager
   */
  protected $cacheContextManager;

  public function __construct(CacheContextsManager $cache_contexts_manager) {
    $this->cacheContextsManager = $cache_contexts_manager;
  }

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

  public static function hashedCurrentPath() {
    $path = Url::fromRoute('<current>')->getInternalPath();
    return crc32($path);
  }

  public function createCacheID(array &$elements) {
    // If the maximum age is zero, then caching is effectively prohibited.
    if (isset($elements['#cache']['max-age']) && $elements['#cache']['max-age'] === 0) {
      return FALSE;
    }

    $cid_parts = [];
    if (isset($elements['#cache']['keys'])) {
      $cid_parts = $elements['#cache']['keys'];
    }
    if (!empty($elements['#cache']['contexts'])) {
      $context_cache_keys = $this->cacheContextsManager->convertTokensToKeys($elements['#cache']['contexts']);
      $cid_parts = array_merge($cid_parts, $context_cache_keys->getKeys());
      CacheableMetadata::createFromRenderArray($elements)
        ->merge($context_cache_keys)
        ->applyTo($elements);
    }
    return implode(':', $cid_parts);
  }
}