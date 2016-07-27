<?php

namespace Drupal\wcr\Plugin;

use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;

/**
 * HTMLMainContentFormatter plugin manager.
 */
class HTMLMainContentFormatterManager extends DefaultPluginManager {

  /**
   * Constructs an IcecreamManager object.
   *
   * @param \Traversable $namespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations,
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   Cache backend instance to use.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler to invoke the alter hook with.
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    parent::__construct('Plugin/wcr/HTMLMainContentFormatter',
                        $namespaces,
                        $module_handler,
                        'Drupal\wcr\Plugin\HTMLMainContentFormattersInterface',
                        'Drupal\wcr\Annotation\HTMLMainContentFormatter');

    $this->alterInfo('html_main_content_formatter_info');
    $this->setCacheBackend($cache_backend, 'html_main_content_formatters');
  }
}