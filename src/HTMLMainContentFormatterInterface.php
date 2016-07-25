<?php
/**
 * @file
 * Provides Drupal\wcr\HTMLMainContentFormatterInterface
 */

namespace Drupal\wcr;

use Drupal\Component\Plugin\PluginInspectionInterface;

/**
 * Defines an interface for HTMLMainContentFormatter plugins.
 */
interface HTMLMainContentFormatterInterface extends PluginInspectionInterface {

  /**
   * Return the name of the plugin.
   *
   * @return string
   */
  public function getName();

  /**
   * Generate the response in the designated format.
   *
   * @return Response
   */
  public function response();

}
