<?php

namespace Drupal\uhsg_news\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Link;
use Drupal\node\Entity\Node;
use Drupal\views\Views;

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
    $renderableArray = [];

    // get link to news view
    $view = Views::getView('news');
    $view->setDisplay('page_1');
    $url = $view->getUrl();
    $url->setOptions([
      'attributes' => [
        'class' => [
          'box-subtitle__link',
          'button--action',
          'icon--arrow-right',
          'theme-transparent',
          'is-center-mobile'
        ],
      ],
    ]);
    $link = Link::fromTextAndUrl($this->t('More current topics'), $url)->toString();

    $targeted_news = \Drupal::service('uhsg_news.targeted_news');
    $nids = $targeted_news->getTargetedNewsNids();

    if ($nids) {
      $nodes = Node::loadMultiple($nids);
      $render_controller = \Drupal::entityTypeManager()->getViewBuilder('node');
      $render_output = $render_controller->viewMultiple($nodes, 'teaser');

      $renderableArray = [
        '#attributes' => [
          'class' => ['clearfix', 'tube'],
        ],
        '#cache' => [
          'tags' => ['node_list'],
          'contexts' => ['active_degree_programme'],
        ],
        'content' => $render_output,
        '#suffix' => $link
      ];
    }

    return $renderableArray;
  }

  /**
   * @inheritdoc
   */
  public function getCacheContexts() {
    $cache_contexts = ['active_degree_programme'];
    return Cache::mergeContexts(parent::getCacheContexts(), $cache_contexts);
  }

}
