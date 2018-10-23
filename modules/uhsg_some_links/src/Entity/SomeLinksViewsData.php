<?php

namespace Drupal\uhsg_some_links\Entity;

use Drupal\views\EntityViewsData;
use Drupal\views\EntityViewsDataInterface;

/**
 * Provides Views data for Some links entities.
 */
class SomeLinksViewsData extends EntityViewsData implements EntityViewsDataInterface {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    $data['some_links']['table']['base'] = [
      'field' => 'id',
      'title' => $this->t('Some links'),
      'help' => $this->t('The Some links ID.'),
    ];

    return $data;
  }

}
