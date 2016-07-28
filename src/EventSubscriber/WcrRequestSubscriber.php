<?php

/**
 * @file
 * Contains \Drupal\copage\EventSubscriber\HtmlResponseSubscriber.
 */

namespace Drupal\wcr\EventSubscriber;

use Drupal\Core\Render\HtmlResponse;
use Drupal\wcr\Service\Utilities;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class WcrRequestSubscriber implements EventSubscriberInterface {

  public function onRespond(GetResponseEvent $event) {
    $request = $event->getRequest();
    //TODO: unset _wcr parameters.
  }


  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    // Run just before \Drupal\Core\EventSubscriber\HtmlResponseSubscriber
    // (priority 0), which invokes
    // \Drupal\Core\Render\HtmlResponseAttachmentsProcessor::processAttachments,
    // which is what processes all attachments into a final HTML response.
    $events[KernelEvents::REQUEST][] = ['onRequest', 1000];

    return $events;
  }

}
