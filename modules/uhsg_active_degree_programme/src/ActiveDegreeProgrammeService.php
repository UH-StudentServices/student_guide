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
  public function getActiveDegreeProgramme() {
    $node = \Drupal::routeMatch()->getParameter('node');
    $term_from_route = \Drupal::routeMatch()->getParameter('taxonomy_term');

    // if page is node first look if degree programme cookie matches any of the referenced degree programmes and use it.
    // if no match is found, set the first degree programme reference as the new active one.
    $reference_fields = array('field_article_degree_programme', 'field_news_degree_programme');
    foreach ($reference_fields as $reference_field) {
      if ($node && $node->hasField($reference_field) && isset($node->get($reference_field)->target_id)) {

        if (isset($_COOKIE['Drupal_visitor_degree_programme'])) {
          foreach ($node->$reference_field as $item) {
            if ($item->target_id == $_COOKIE['Drupal_visitor_degree_programme']) {
              $term = \Drupal\taxonomy\Entity\Term::load($item->target_id);
              break;
            }
          }
        }

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

    // if page is degree programme term, make that degree programme active
    if ($term_from_route && $term_from_route->bundle() == 'degree_programme') {
      $term = $term_from_route;
      $tid = $term->id();
      $cookie = array('degree_programme' => $tid);
      user_cookie_save($cookie);
      return $term->getName();
    }

    // if page doesn't have a degree programme reference, or it isn't a degree programme term,
    // read active degree programme from cookie. If no cookie fallback to "select degree programme".
    else {
      if (isset($_COOKIE['Drupal_visitor_degree_programme'])) {
        $term = \Drupal\taxonomy\Entity\Term::load($_COOKIE['Drupal_visitor_degree_programme']);
        if ($term) {
          $term_translated = \Drupal::service('entity.repository')->getTranslationFromContext($term);
          return $term_translated->getName();
        }
      }
    }

    return NULL;
  }
 
}
