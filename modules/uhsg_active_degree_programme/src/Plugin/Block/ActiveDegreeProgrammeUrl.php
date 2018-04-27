<?php

namespace Drupal\uhsg_active_degree_programme\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Provides a 'active_degree_programme_url' block.
 *
 * @Block(
 *  id = "active_degree_programme_url",
 *  admin_label = @Translation("Active degree programme URL"),
 * )
 */
class ActiveDegreeProgrammeUrl extends BlockBase {

  use StringTranslationTrait;

  public function build() {
    return [
      'content' => [
        'button' => [
          '#type' => 'button',
          '#value' => $this->t('Copy URL to clipboard'),
          '#attributes' => [
            'id' => 'copy-url'
          ],
          '#attached' => [
            'library' => [
              'uhsg_active_degree_programme/copy_url'
            ],
            'drupalSettings' => [
              'uhsg_active_degree_programme' => [
                'selector' => '[rel="shortlink-with-degree-programme"]'
              ]
            ]
          ]
        ]
      ]
    ];
  }
}
