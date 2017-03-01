<?php

/**
 * Implements hook_flag_type_info_alter().
 */
function uhsg_degree_programme_flag_type_info_alter(array &$definitions) {
  if (isset($definitions['entity:taxonomy_term'])) {
    $definitions['entity:taxonomy_term']['class'] = '\\Drupal\\uhsg_degree_programme\\Flag\\MyDegreeProgrammeFlagType';
  }
}
