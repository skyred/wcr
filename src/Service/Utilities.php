<?php

/**
 * @file
 * Contains \Drupal\wcr\Service\Utilities
 */

namespace Drupal\wcr\Service;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Cache\Context\CacheContextsManager;
use Drupal\Core\Render\BubbleableMetadata;
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

  public static function currentPath() {
    $current = Url::fromRoute('<current>');
    $params = \Drupal::request()->query->all();
    if (isset($params['_wrapper_format']))
      unset($params['_wrapper_format']);
    if (isset($params['mode']))
      unset($params['mode']);
    if (isset($params['block']))
      unset($params['block']);

    return Url::fromRoute('<current>')->getInternalPath() . implode(':', $params);
  }

  public static function hashedCurrentPath() {
    $path = self::currentPath();
    return self::hash($path);
  }

  public static function hash($str) {
    return dechex(crc32($str));
  }

  public function createBlockID(array &$elements) {
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

  public static function getJsAssetsFromRenderArray($render_array) {
    $attachments = BubbleableMetadata::createFromRenderArray($render_array)->getAttachments();
    return self::getJsAssetsFromMetadata($attachments);
  }

  public static function getJsAssetsFromMetadata($attachments) {
    $tmp_response = new AjaxResponse();
    $tmp_response->setAttachments(array_intersect_key($attachments, array_flip(['library'])));
    \Drupal::service('ajax_response.attachments_processor')->processAttachments($tmp_response);
    $commands = $tmp_response->getCommands();

    $html = '';
    foreach ($commands as $item) {
      if ($item['command'] == 'insert') {
        $html .= $item['data'];
      }
    }
    // VERY SLOW!!!
    $scripts = [];
    $doc = new \DOMDocument();
    $doc->loadHTML($html);

    foreach($doc->getElementsByTagName('script') as $script) {
      if($script->hasAttribute('src')) {
        $scripts[] = $script->getAttribute('src');
      }
    }
    return $scripts;
  }
}