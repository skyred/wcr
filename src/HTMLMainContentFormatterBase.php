<?php
/**
 * @file
 * Provides Drupal\wcr\HTMLMainContentFormatterBase.
 */

namespace Drupal\wcr;

use Drupal\Component\Plugin\PluginBase;

class HTMLMainContentFormatterBase extends PluginBase implements HTMLMainContentFormatterInterface {
  protected $wcrUtilities;
  
  function __construct() {
    $this->wcrUtilities = \Drupal::service('wcr.utilities');
  }

  public function getName() {
    return $this->pluginDefinition['name'];
  }

  public function handle(array $main_content, Request $request, RouteMatchInterface $route_match) {
    // Do nothing.
  }
}
