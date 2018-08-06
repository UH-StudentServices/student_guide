<?php

namespace Drupal\uhsg_degree_programme\Plugin\views\sort;

use Drupal\views\Plugin\views\sort\SortPluginBase;

/**
 * Sort by degree programme type using pre-determined order weights ('bachelor',
 * 'master', 'doctoral').
 *
 * @ViewsSort("degree_programme_type")
 */
class DegreeProgrammeType extends SortPluginBase {

  public function query() {
    $this->ensureMyTable();
    $formula = "FIELD($this->tableAlias.$this->realField, 'bachelor', 'master', 'doctoral')";

    $this->query->addOrderBy(NULL, $formula, $this->options['order'], 'degree_programme_type_sort_weight');
  }
}
