<?php
/**
 * @file
 * Provides Drupal\wcr\RenderArrayFormatterBase.
 */

namespace Drupal\wcr\Plugin;

use Drupal\Component\Plugin\PluginBase;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Routing\RouteMatchInterface;

class RenderArrayFormatterBase extends PluginBase implements RenderArrayFormatterInterface {
  protected $wcrUtilities;
  
  function __construct(array $configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->wcrUtilities = \Drupal::service('wcr.utilities');
  }

  public function getName() {
    return $this->pluginDefinition['name'];
  }

  public function generateResponse(array $renderArray, array $options = []) {
    // Do nothing.
  }

}
