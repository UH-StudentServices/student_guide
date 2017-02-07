<?php

namespace Drupal\uhsg_news\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;

/**
 * Provides a 'news_per_degree_programme' block.
 *
 * @Block(
 *  id = "news_per_degree_programme",
 *  admin_label = @Translation("News per degree programme"),
 * )
 */
class NewsPerDegreeProgramme extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $lang = \Drupal::languageManager()->getCurrentLanguage()->getId();

    $query = \Drupal::entityQuery('node')
      ->condition('status', 1)
      ->condition('langcode', $lang)
      ->condition('type', 'news')
      ->sort('created', 'DESC');
    $group = $query->orConditionGroup()
      ->condition('field_news_degree_programme', NULL, 'IS NULL');

    // if on term page, add tid to condition group
    $tid = \Drupal::service('uhsg_active_degree_programme.active_degree_programme')->getId();
    if ($tid) {
      $group->condition('field_news_degree_programme', $tid);
    }

    $nids = $query->condition($group)->execute();

    if ($nids) {
      $nodes = \Drupal\node\Entity\Node::loadMultiple($nids);
      $render_controller = \Drupal::entityTypeManager()->getViewBuilder('node');
      $render_output = $render_controller->viewMultiple($nodes, 'teaser');

      return array(
        '#attributes' => [
          'class' => ['grid-container'],
        ],
        '#cache' => [
          'tags' => ['node_list'],
          'contexts' => ['active_degree_programme'],
        ],
        'content' => $render_output,
      );
    }
  }

  /**
   * @inheritdoc
   */
  public function getCacheContexts() {
    $cache_contexts = ['active_degree_programme'];
    return Cache::mergeContexts(parent::getCacheContexts(), $cache_contexts);
  }

}
