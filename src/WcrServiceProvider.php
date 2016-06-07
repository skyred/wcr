<?php
/**
 * @file
 * Contains Drupal\twig_polymer\TwigPolymerServiceProvider.
 */

namespace Drupal\wcr;

use Drupal\Core\DependencyInjection\ServiceProviderBase;
use Drupal\Core\DependencyInjection\ContainerBuilder;

/**
 * Class WcrServiceProvider
 */
class WcrServiceProvider extends ServiceProviderBase {
  /**
   * @param \Drupal\Core\DependencyInjection\ContainerBuilder $container
   */
  public function alter(ContainerBuilder $container) {
    // Override the twig class, to use our own TwigEnvironment.
    $definition = $container->getDefinition('renderer');
    $definition->setClass('Drupal\wcr\Renderer\TrackableRenderer');
  }
}
