<?php

namespace Drupal\uhsg_news\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Link;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\node\Entity\Node;
use Drupal\views\Views;

class NewsBlockBase extends BlockBase {

  use StringTranslationTrait;

  public function build() {
    // Implemented in subclasses.
  }

  /**
   * @inheritdoc
   */
  public function getCacheContexts() {
    return Cache::mergeContexts(parent::getCacheContexts(), ['active_degree_programme']);
  }

  protected function render($nids) {
    $renderableArray = $this->getBaseRenderableArray();

    if ($nids) {
      $renderableArray['content'] = $this->renderContent($nids);
      $renderableArray['#suffix'] = $this->getLinkToMoreContent();
    }

    return $renderableArray;
  }

  private function getBaseRenderableArray() {
    return [
      '#attributes' => [
        'class' => ['clearfix', 'tube'],
      ],
      '#cache' => [
        'tags' => ['node_list'],
        'contexts' => ['active_degree_programme'],
      ],
      'content' => ['#markup' => '<div class="view-empty"><h3>' . $this->t('No results') . '</h3></div>']
    ];
  }

  /**
   * @param array $nids
   * @return mixed
   */
  private function renderContent($nids) {
    return \Drupal::entityTypeManager()
      ->getViewBuilder('node')
      ->viewMultiple(Node::loadMultiple($nids), 'teaser');
  }

  /**
   * @return string
   */
  private function getLinkToMoreContent() {
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

    return Link::fromTextAndUrl($this->t('More current topics'), $url)->toString();
  }
}
