<?php
/**
 * @file
 * Contains \Drupal\wcr\Annotation\RenderArrayFormatter
 */

 namespace Drupal\wcr\Annotation;

 use Drupal\Component\Annotation\Plugin;

 /**
  * Defines a formatter for transforming the 
  * main content of a normal HTML, block-based page.
  * Examples: Block list, a single block wrapped as Web Component,
  *           SPF format, etc.
  *
  * Plugin Namespace: Plugin\wcr\RenderArrayFormatter
  *
  * @see plugin_api
  *
  * @Annotation
  **/

class RenderArrayFormatter extends Plugin {
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
   * The description of the formatter.
   *
   * @var \Drupal\Core\Annotation\Translation
   *
   * @ingroup plugin_translatable
   */
  public $description;

}