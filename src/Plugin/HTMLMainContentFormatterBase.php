<?php
/**
 * @file
 * Provides Drupal\wcr\HTMLMainContentFormatterBase.
 */

namespace Drupal\wcr\Plugin;

use Drupal\Component\Plugin\PluginBase;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Routing\RouteMatchInterface;

class HTMLMainContentFormatterBase extends PluginBase implements HTMLMainContentFormatterInterface {
  protected $wcrUtilities;
  
  function __construct(array $configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->wcrUtilities = \Drupal::service('wcr.utilities');
  }

  public function getName() {
    return $this->pluginDefinition['name'];
  }

  public function handle(array $main_content, Request $request, RouteMatchInterface $route_match) {
    // Do nothing.
  }

}
