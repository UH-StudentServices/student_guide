<?php

namespace Drupal\uhsg_news\Plugin\Block;

/**
 * Provides a 'news_per_degree_programme' block.
 *
 * @Block(
 *  id = "news_per_degree_programme",
 *  admin_label = @Translation("News per degree programme"),
 * )
 */
class NewsPerDegreeProgramme extends NewsBlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $activeDegreeProgrammeTerm = \Drupal::service('uhsg_active_degree_programme.active_degree_programme')->getTerm();

    $isDegreeStudentProgramme = $activeDegreeProgrammeTerm
      && $activeDegreeProgrammeTerm->get('field_degree_programme_type')->value !== 'doctoral';

    if ($isDegreeStudentProgramme) {
      return $this->render(\Drupal::service('uhsg_news.news')->getProgrammeNewsNids());
    }

    return [];
  }

}
