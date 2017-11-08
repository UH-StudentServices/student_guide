<?php

namespace Drupal\uhsg_redirect_to_login\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

class RouteSubscriber extends RouteSubscriberBase {
  /**
   * {@inheritdoc}
   */
  public function alterRoutes(RouteCollection $collection) {
    $routesToChange = ['samlauth.saml_controller_login', 'samlauth.saml_controller_acs'];
    foreach ($routesToChange as $routeName) {
      if ($route = $collection->get($routeName)) {
        // HUB-246: In some cases, user may hit the redirect logic during
        // bootstrapping even when user is logged in. As it is expensive process
        // to check there whether to redirect or not, we allow user to access
        // saml login at any role.
        $anonymousOrAuthenticatedRole = 'anonymous+authenticated';
        $route->setRequirement('_role', $anonymousOrAuthenticatedRole);
      }
    }
  }
}
