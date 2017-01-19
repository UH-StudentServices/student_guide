<?php

/**
 * @file
 * Contains \Drupal\uhsg_active_degree_programme\ActiveDegreeProgrammeService.
 */
 
namespace Drupal\uhsg_active_degree_programme;
 
class ActiveDegreeProgrammeService {

  /**
   * Set active degree programme.
   */
  public function set($term) {
    $tid = $term->id();
    $cookie = array('degree_programme' => $tid);
    user_cookie_save($cookie);
  }

  /**
   * Return name of active degree programme.
   */
  public function getName() {

    // if term id is in route parameters, it is active.
    $param = \Drupal::service('current_route_match')->getParameters()->get('tid');
    if ($param) {
      $term = \Drupal\taxonomy\Entity\Term::load($param);
      $this->set($term);
      return \Drupal::service('entity.repository')->getTranslationFromContext($term)->getName();
    }

    // If page is degree programme term, get name from there.
    // This means the user has changed the active degree programme.
    $term = \Drupal::routeMatch()->getParameter('taxonomy_term');
    if ($term && $term->bundle() == 'degree_programme') {
      $this->set($term);
      return $term->getName();
    }

    // as a fallback term is set in cookie, lets use that if nothing else works.
    // This happens on the front page and theme nodes, also on news and article nodes
    // when tid is not in route parameters.
    if (isset($_COOKIE['Drupal_visitor_degree_programme'])) {
      $term = \Drupal\taxonomy\Entity\Term::load($_COOKIE['Drupal_visitor_degree_programme']);
      return \Drupal::service('entity.repository')->getTranslationFromContext($term)->getName();
    }

    return NULL;
  }
 
}
