<?php

/**
 * @file
 * Contains \Drupal\copage\EventSubscriber\HtmlResponseSubscriber.
 */

namespace Drupal\copage\EventSubscriber;

use Drupal\Core\Render\HtmlResponse;
use Drupal\wcr\Service\Utilities;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class HtmlResponseSubscriber implements EventSubscriberInterface {

  /**
   * Processes HTML responses to allow Refreshless' JavaScript to work.
   *
   * @param \Symfony\Component\HttpKernel\Event\FilterResponseEvent $event
   *   The event to process.
   */
  public function onRespond(FilterResponseEvent $event) {
    $response = $event->getResponse();
    if (!$response instanceof HtmlResponse) {
      return;
    }

    $this->addBlockList($response);
  }


  protected function addBlockList(HtmlResponse $response) {
    $scripts = Utilities::getJsAssetsFromMetadata($response->getAttachments());
    $response->addAttachments(['drupalSettings' => ['jsAssets' => $scripts]]);
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    // Run just before \Drupal\Core\EventSubscriber\HtmlResponseSubscriber
    // (priority 0), which invokes
    // \Drupal\Core\Render\HtmlResponseAttachmentsProcessor::processAttachments,
    // which is what processes all attachments into a final HTML response.
    $events[KernelEvents::RESPONSE][] = ['onRespond', 1];

    return $events;
  }

}
