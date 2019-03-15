<?php

use Drupal\taxonomy\Entity\Term;

/**
 * Implements hook_drush_command().
 */
function uhsg_other_education_provider_drush_command() {
 $items = [];
 $items['uhsg-create-other-education-providers'] = [
   'description' => 'Create default other education provider terms.',
 ];
 return $items;
}

/**
 * Create default other education provider terms.
 */
function drush_uhsg_other_education_provider_uhsg_create_other_education_providers() {
  $other_education_providers = [
    [
      'en' => 'Open University',
      'fi' => 'Avoin yliopisto',
      'sv' => 'Öppna universitet'
    ],
    [
      'en' => 'Language Centre',
      'fi' => 'Kielikeskus',
      'sv' => 'Språkcentrum'
    ],
  ];

  foreach ($other_education_providers as $other_education_provider) {
    $term = Term::create([
      'name' => $other_education_provider['en'],
      'vid' => 'other_education_provider',
    ]);
    $term->addTranslation('fi', ['name' => $other_education_provider['fi']]);
    $term->addTranslation('sv', ['name' => $other_education_provider['sv']]);
    $term->save();
  }
}
