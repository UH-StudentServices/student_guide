<?php

/**
 * Implements hook_drush_command().
 */
function uhsg_domain_drush_command() {
 $items = array();
 $items['uhsg-assign-article-domains'] = [
   'description' => 'Assign domains for published articles.',
   'arguments' => [
      'limit' => 'The max number of articles to process.',
    ],
 ];
 return $items;
}

function drush_uhsg_domain_uhsg_assign_article_domains($limit = 100) {
  $nids = \Drupal::entityQuery('node')
    ->condition('type', 'article')
    ->condition('status', 1)
    ->notExists('field_article_domain')
    ->range(0, $limit)
    ->execute();

  $nodes = \Drupal\node\Entity\Node::loadMultiple($nids);

  foreach ($nodes as $node) {
    $node->save();
  }
}
