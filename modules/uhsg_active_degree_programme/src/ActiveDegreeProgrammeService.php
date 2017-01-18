<?php

/**
 * @file
 * Contains \Drupal\uhsg_active_degree_programme\ActiveDegreeProgrammeService.
 */
 
namespace Drupal\uhsg_active_degree_programme;
 
class ActiveDegreeProgrammeService {
  
  /**
   * Return name of active degree programme.
   */
  public function getName() {
    $node = \Drupal::routeMatch()->getParameter('node');
    $term = \Drupal::routeMatch()->getParameter('taxonomy_term');

    // if page is node and has a valid term, use it
    if ($node && $this->getFromNode($node)) {
      return $this->getFromNode($node);
    }

    // if page is degree programme term, get name from there
    if ($term && $term->bundle() == 'degree_programme') {
      return $this->getFromTerm($term);
    }

    // if page doesn't have a degree programme reference, or it isn't a degree programme term,
    // read active degree programme from cookie. If no cookie fallback to "select degree programme".
    if (isset($_COOKIE['Drupal_visitor_degree_programme'])) {
      return $this->getFromCookie($_COOKIE['Drupal_visitor_degree_programme']);
    }

    return NULL;
  }

  private function getFromNode($node) {
    $reference_fields = array('field_article_degree_programme', 'field_news_degree_programme');
    foreach ($reference_fields as $reference_field) {
      if ($node && $node->hasField($reference_field) && isset($node->get($reference_field)->target_id)) {

        // if cookie is set, search for match in reference field
        if (isset($_COOKIE['Drupal_visitor_degree_programme'])) {
          foreach ($node->$reference_field as $item) {
            if ($item->target_id == $_COOKIE['Drupal_visitor_degree_programme']) {
              $term = \Drupal\taxonomy\Entity\Term::load($item->target_id);
              break;
            }
          }
        }

        //if no match is found, set the first degree programme reference as the new active one.
        if (empty($term)) {
          $term = \Drupal\taxonomy\Entity\Term::load($node->get($reference_field)->target_id);
        }

        $tid = $term->id();
        $cookie = array('degree_programme' => $tid);
        user_cookie_save($cookie);

        $term_translated = \Drupal::service('entity.repository')->getTranslationFromContext($term);
        return $term_translated->getName();
      }
    }
    return NULL;
  }

  private function getFromTerm($term) {
    $tid = $term->id();
    $cookie = array('degree_programme' => $tid);
    user_cookie_save($cookie);
    return $term->getName();
  }

  private function getFromCookie($cookie) {
    $term = \Drupal\taxonomy\Entity\Term::load($cookie);
    if ($term) {
      $term_translated = \Drupal::service('entity.repository')->getTranslationFromContext($term);
      return $term_translated->getName();
    }
  }
 
}
