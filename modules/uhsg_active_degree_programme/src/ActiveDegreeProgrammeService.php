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
  private function set($term) {
    $tid = $term->id();
    $cookie = array('degree_programme' => $tid);
    user_cookie_save($cookie);
  }

  /**
   * Return name of active degree programme.
   */
  public function getName() {
    $term = $this->getTerm();
    if ($term) {
      return \Drupal::service('entity.repository')->getTranslationFromContext($term)->getName();
    }
  }

  /**
   * Return id of active degree programme.
   */
  public function getId() {
    $term = $this->getTerm();
    if ($term) {
      return $term->id();
    }
  }

  /**
   * Return term of active degree programme.
   */
  private function getTerm() {

    // if term is in get parameters, it is active.
    $query_param = \Drupal::Request()->get('degree_programme');
    if ($query_param) {
      $term = \Drupal\taxonomy\Entity\Term::load($query_param);
      $this->set($term);
      return $term;
    }

    // if term id is in route parameters, it is active.
    $param = \Drupal::service('current_route_match')->getParameters()->get('tid');
    if ($param) {
      $term = \Drupal\taxonomy\Entity\Term::load($param);
      $this->set($term);
      return $term;
    }

    // If page is degree programme term, get name from there.
    // This means the user has changed the active degree programme.
    $term = \Drupal::routeMatch()->getParameter('taxonomy_term');
    if ($term && $term->bundle() == 'degree_programme') {
      $this->set($term);
      return $term;
    }

    // as a fallback term is set in cookie, lets use that if nothing else works.
    // This happens on the front page and theme nodes, also on news and article nodes
    // when tid is not in route parameters.
    if (isset($_COOKIE['Drupal_visitor_degree_programme'])) {
      $term = \Drupal\taxonomy\Entity\Term::load($_COOKIE['Drupal_visitor_degree_programme']);
      return $term;
    }

    return NULL;
  }
}
