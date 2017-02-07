<?php

namespace Drupal\uhsg_samlauth\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

class RouteSubscriber extends RouteSubscriberBase {
  /**
   * {@inheritdoc}
   */
  public function alterRoutes(RouteCollection $collection) {
    // Replace saml login with own implementation that has additional features
    if ($route = $collection->get('samlauth.saml_controller_login')) {
      $route->setDefault('_controller', 'Drupal\\uhsg_samlauth\\Controller\\SamlController::login');
    }
  }
}
