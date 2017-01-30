<?php

namespace Drupal\uhsg_active_degree_programme\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\taxonomy\Entity\Term;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Controller routines for active degree programme routes.
 */
class ActiveDegreeProgrammeController extends ControllerBase {
  public function setActiveDegreeProgramme($tid) {
    $term = Term::load($tid);
    if ($term) {
      \Drupal::service('uhsg_active_degree_programme.active_degree_programme')->set($term);
    }
    return new RedirectResponse(\Drupal::request()->server->get('HTTP_REFERER'));
  }
}
