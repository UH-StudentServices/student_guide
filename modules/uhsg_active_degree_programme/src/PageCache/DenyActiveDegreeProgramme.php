<?php

namespace Drupal\uhsg_active_degree_programme\PageCache;

use Drupal\Core\PageCache\ResponsePolicyInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DenyActiveDegreeProgramme implements ResponsePolicyInterface {

  /**
   * @inheritdoc
   */
  public function check(Response $response, Request $request) {
    // If we find degree programme values either from cookies or headers, do not
    // allow page caching.
    if ($request->cookies->get('Drupal_visitor_degree_programme') || $request->headers->get('x-degree-programme')) {
      return self::DENY;
    }
  }

}
