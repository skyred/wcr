<?php
/**
 * @file
 * Provides Drupal\wcr\RenderArrayFormatterInterface
 */

namespace Drupal\wcr\Plugin;

use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

/**
 * Defines an interface for RenderArrayFormatter plugins.
 */
interface RenderArrayFormatterInterface extends PluginInspectionInterface {

  /**
   * Return the name of the plugin.
   *
   * @return string
   */
  public function getName();

  /**
   * Generate the response in the designated format.
   *
   * @param array $renderArray
   * @param array $options
   * @return \Symfony\Component\HttpFoundation\Response
   */
  public function generateResponse(array $renderArray, array $options = []);

}
