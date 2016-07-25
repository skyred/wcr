<?php

namespace Drupal\copageS\EventSubscriber;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpFoundation\RedirectResponse;

class RedirectResponseSubscriber implements EventSubscriberInterface {

  public function onRedirectResponse(FilterResponseEvent $event) {
    $response = $event->getResponse();
    $format = \Drupal::request()->get('_wrapper_format');
    if ($format != 'drupal_components') {
      return;
    }
    if ($response instanceof RedirectResponse) {
      $destination = $response->getTargetUrl();

      $response = new Response();
      $response->setStatusCode(Response::HTTP_OK);
      $response->headers->set('Content-Type', 'application/json');
      $response->setContent(json_encode([
        'redirect' => $destination,
      ]));
      $event->setResponse($response);
    }

  }

  static function getSubscribedEvents() {
    $events[KernelEvents::RESPONSE][] = ['onRedirectResponse', -10];
    return $events;
  }

}
