<?php

/**
 * @file
 * Contains \Drupal\uhsg_active_degree_programme\ActiveDegreeProgrammeService.
 */
 
namespace Drupal\uhsg_active_degree_programme;

use Drupal\taxonomy\Entity\Term;
 
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
  public function getTerm() {

    // First check from parameters
    $query_param = \Drupal::Request()->get('degree_programme');
    if ($query_param) {
      $term = Term::load($query_param);
      return $term;
    }

    // Secondly check from X-Headers
    if (!empty($_SERVER['HTTP_X_DEGREE_PROGRAMME'])) {
      $term = Term::load($_SERVER['HTTP_X_DEGREE_PROGRAMME']);
      return $term;
    }

    // Thirdly check from cookies
    if (isset($_COOKIE['Drupal_visitor_degree_programme'])) {
      $term = Term::load($_COOKIE['Drupal_visitor_degree_programme']);
      return $term;
    }

    // TODO: Fourthly check from logged in user study rights

    return NULL;
  }
}
