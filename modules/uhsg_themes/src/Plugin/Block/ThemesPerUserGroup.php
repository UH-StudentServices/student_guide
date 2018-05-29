<?php

namespace Drupal\uhsg_themes\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\views\Views;

/**
 * Provides a 'themes_per_user_group' block.
 *
 * @Block(
 *  id = "themes_per_user_group",
 *  admin_label = @Translation("Themes per user group"),
 * )
 */
class ThemesPerUserGroup extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    return $this->render();
  }

  private function render() {
    $renderableArray = [];
    $renderableArray['content'] = $this->renderContent();

    return $renderableArray;
  }

  private function renderContent() {
    $view = Views::getView('themes');

    return [
      'degree_students' => $view->render('degree_students'),
      'doctoral_candidates' => $view->render('doctoral_candidates'),
      'specialist_training' => $view->render('specialist_training')
    ];
  }
}
