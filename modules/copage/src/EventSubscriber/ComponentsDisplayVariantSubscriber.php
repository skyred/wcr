<?php
/**
 * @file
 * Contains Drupal\copage\EventSubscriber\ComponentsDisplayVariantSubscriber.
 */

namespace Drupal\copage\EventSubscriber;

use Drupal\Core\Render\PageDisplayVariantSelectionEvent;
use Drupal\Core\Render\RenderEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ComponentsDisplayVariantSubscriber implements EventSubscriberInterface {

  public function onBlockPageDisplayVariantSelected(PageDisplayVariantSelectionEvent $event) {
    // Only activate when Block is enabled.
    $path = \Drupal::request()->getPathInfo();
    if (\Drupal::theme()->getActiveTheme()->getName() != 'polymer') {
      // TEMP: skip admin pages and non-supported themes
      return;
    }
    $format = \Drupal::request()->get('_wrapper_format');
    if ($format != 'drupal_block' && $format != 'drupal_components' && $event->getPluginId() === 'block_page') {
      $event->setPluginId('components_display');
    }
  }

  /**
   * {@inheritdoc}
   */
  static function getSubscribedEvents() {
    // Set a very low priority, so that it runs last.
   // if (Drupal::request()->getFormat())
    $events[RenderEvents::SELECT_PAGE_DISPLAY_VARIANT][] = ['onBlockPageDisplayVariantSelected', -10000];
    return $events;
  }

}
