<?php

namespace Drupal\uhsg_news\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Link;

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
      ->sort('created', 'DESC')
      ->range(0, 3);
    $group = $query->orConditionGroup()
      ->condition('field_news_degree_programme', NULL, 'IS NULL');

    // if on term page, add tid to condition group
    $tid = \Drupal::service('uhsg_active_degree_programme.active_degree_programme')->getId();
    if ($tid) {
      $group->condition('field_news_degree_programme', $tid);
    }

    // get link to news view
    $view = \Drupal\views\Views::getView('news');
    $view->setDisplay('page_1');
    $url = $view->getUrl();
    $url->setOptions(array(
      'attributes' => array(
        'class' => array(
          'box-subtitle__link',
          'button--action',
          'icon--arrow-right',
          'theme-transparent',
          'is-center-mobile'
        ),
      ),
    ));
    $link = Link::fromTextAndUrl(t('More current topics'), $url)->toString();

    $nids = $query->condition($group)->execute();

    if ($nids) {
      $nodes = \Drupal\node\Entity\Node::loadMultiple($nids);
      $render_controller = \Drupal::entityTypeManager()->getViewBuilder('node');
      $render_output = $render_controller->viewMultiple($nodes, 'teaser');

      return array(
        '#attributes' => [
          'class' => ['clearfix'],
        ],
        '#cache' => [
          'tags' => ['node_list'],
          'contexts' => ['active_degree_programme'],
        ],
        'content' => $render_output,
        '#suffix' => $link
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
