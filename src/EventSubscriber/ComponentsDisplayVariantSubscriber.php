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
    if ($event->getPluginId() === 'block_page') {
      $event->setPluginId('components_display');
    }
  }

  /**
   * {@inheritdoc}
   */
  static function getSubscribedEvents() {
    // Set a very low priority, so that it runs last.
    $events[RenderEvents::SELECT_PAGE_DISPLAY_VARIANT][] = ['onBlockPageDisplayVariantSelected', -10000];
    return $events;
  }

}
