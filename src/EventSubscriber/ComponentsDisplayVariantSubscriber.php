<?php
/**
 * @file
 * Contains Drupal\wcr\EventSubscriber\ComponentsDisplayVariantSubscriber.
 */

namespace Drupal\wcr\EventSubscriber;

use Drupal\Core\Render\PageDisplayVariantSelectionEvent;
use Drupal\Core\Render\RenderEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ComponentsDisplayVariantSubscriber implements EventSubscriberInterface {

  public function onBlockPageDisplayVariantSelected(PageDisplayVariantSelectionEvent $event) {
    // Only activate when Block is enabled.
    $path = \Drupal::request()->getPathInfo();
    if (0 === strpos($path, '/admin')) {
      // TEMP: skip admin pages
      return;
    }
    $format = \Drupal::request()->get('_wrapper_format');
    if ($format != 'drupal_block' && $event->getPluginId() === 'block_page') {
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
