<?php

namespace Drupal\uhsg_news\Plugin\Block;

/**
 * Provides a 'general_news' block.
 *
 * @Block(
 *  id = "general_news",
 *  admin_label = @Translation("General news"),
 * )
 */
class GeneralNews extends NewsBlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    return $this->render(\Drupal::service('uhsg_news.news')->getGeneralNewsNids());
  }
}
