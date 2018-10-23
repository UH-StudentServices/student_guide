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
    return ['content' => $this->render()];
  }

  private function render() {
    return [
      'degree_students' => $this->renderDisplay('degree_students'),
      'doctoral_candidates' => $this->renderDisplay('doctoral_candidates'),
      'specialist_training' => $this->renderDisplay('specialist_training'),
    ];
  }

  private function renderDisplay($displayId) {
    $view = Views::getView('themes');
    $view->setDisplay($displayId);

    return $view->buildRenderable();
  }

}
