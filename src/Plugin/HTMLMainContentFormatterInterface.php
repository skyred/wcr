<?php
/**
 * @file
 * Provides Drupal\wcr\HTMLMainContentFormatterInterface
 */

namespace Drupal\wcr\Plugin;

use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

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
  public function handle(array $main_content, Request $request, RouteMatchInterface $route_match);

}
