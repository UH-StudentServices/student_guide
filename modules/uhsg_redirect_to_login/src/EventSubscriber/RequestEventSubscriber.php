<?php

namespace Drupal\uhsg_redirect_to_login\EventSubscriber;

use Drupal\Core\Routing\TrustedRedirectResponse;
use Drupal\Core\Url;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class RequestEventSubscriber implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    // Set priority higher than DynamicPageCacheSubscriber (27), but lower than
    // RedirectResponseSubscriber (100).
    $events[KernelEvents::REQUEST][] = ['onRequest', 50];
    return $events;
  }

  /**
   * Respond to incoming requests that should be redirected to login.
   *
   * As we are not allowed to remove cookies specified from top domain, we
   * create second "triggered" cookie that represent the process flow being
   * handled.
   *
   * @param GetResponseEvent $event
   */
  public function onRequest(GetResponseEvent $event) {
    $cookie_name_logged = 'OPINTONI_HAS_LOGGED_IN';
    $cookie_name_triggered = 'OPINTONI_HAS_LOGGED_IN_HAS_TRIGGERED';
    if ($event->getRequest()->cookies->has($cookie_name_logged) && !$event->getRequest()->cookies->has($cookie_name_triggered)) {

      // When cookie has been found, but not yet triggered, create redirect
      // response.
      $response = new TrustedRedirectResponse(Url::fromRoute('samlauth.saml_controller_login')->toString(TRUE)->getGeneratedUrl());

      // Create triggered cookie, so that following requests wouldn't redirect
      $response->headers->setCookie(new Cookie($cookie_name_triggered, 'yes'));

      // Specify caching for the response, so that dynamic caches are generated
      // according to cookie existance/values.
      $response->getCacheableMetadata()->addCacheContexts(['cookies:' . $cookie_name_logged, 'cookies:' . $cookie_name_triggered]);

      // Finally set the response for the request
      $event->setResponse($response);

    }
  }

}
