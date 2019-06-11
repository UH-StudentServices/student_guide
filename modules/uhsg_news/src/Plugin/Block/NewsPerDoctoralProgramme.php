<?php

namespace Drupal\uhsg_news\Plugin\Block;

/**
 * Provides a 'news_per_doctoral_programme' block.
 *
 * @Block(
 *  id = "news_per_doctoral_programme",
 *  admin_label = @Translation("News per doctoral programme"),
 * )
 */
class NewsPerDoctoralProgramme extends NewsBlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $activeDegreeProgrammeTerm = \Drupal::service('uhsg_active_degree_programme.active_degree_programme')->getTerm();

    if (!$activeDegreeProgrammeTerm) {
      return [];
    }

    // Render the block if the active degree programme is a doctoral programme.
    if ($activeDegreeProgrammeTerm->get('field_user_group')->value === 'doctoral_candidates') {
      return $this->render(\Drupal::service('uhsg_news.news')->getProgrammeNewsNids());
    }

    return [];
  }

}
