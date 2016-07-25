<?php
/**
 * @file
 * Contains \Drupal\wcr\Annotation\HTMLMainContentFormatter
 */

 namespace Drupal\wcr\Annotation;

 use Drupal\Component\Annotation\Plugin;

 /**
  * Defines a formatter for transforming the 
  * main content of a normal HTML, block-based page.
  * Examples: Block list, a single block wrapped as Web Component,
  *           SPF format, etc.
  *
  * Plugin Namespace: Plugin\wcr\HTMLMainContentFormatter
  *
  * @see plugin_api
  *
  * @Annotation
  **/

class Flavor extends Plugin {
  /**
   * The plugin ID.
   *
   * @var string
   */
  public $id;

  /**
   * The name of the formatter.
   *
   * @var \Drupal\Core\Annotation\Translation
   *
   * @ingroup plugin_translatable
   */
  public $name;

  /**
   * The command to trigger this plugin.
   *
   * @var string
   */
  public $command;
}