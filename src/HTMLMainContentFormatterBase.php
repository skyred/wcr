<?php
/**
 * @file
 * Provides Drupal\wcr\HTMLMainContentFormatterBase.
 */

namespace Drupal\wcr;

use Drupal\Component\Plugin\PluginBase;

class HTMLMainContentFormatterBase extends PluginBase implements HTMLMainContentFormatterInterface {

  public function getName() {
    return $this->pluginDefinition['name'];
  }

  public function response() {
    
  }
}
