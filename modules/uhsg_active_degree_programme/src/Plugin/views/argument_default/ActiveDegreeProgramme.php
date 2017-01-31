<?php

namespace Drupal\uhsg_active_degree_programme\Plugin\views\argument_default;

use Drupal\Core\Cache\CacheableDependencyInterface;
use Drupal\views\Plugin\views\argument_default\ArgumentDefaultPluginBase;

/**
 * The active degree programme argument default handler.
 *
 * @ingroup views_argument_default_plugins
 *
 * @ViewsArgumentDefault(
 *   id = "active_degree_programme",
 *   title = @Translation("Taxonomy term ID from active degree programme")
 * )
 */
class ActiveDegreeProgramme extends ArgumentDefaultPluginBase implements CacheableDependencyInterface {

  /**
   * {@inheritdoc}
   */
  public function getArgument() {
    // TODO: Can I access the service through dependency injection?
    return \Drupal::service('uhsg_active_degree_programme.active_degree_programme')->getId();
  }

  /**
   * @inheritdoc
   */
  public function getCacheTags() {
    return [];
  }

  /**
   * @inheritdoc
   */
  public function getCacheContexts() {
    return ['active_degree_programme'];
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheMaxAge() {
    return 0;
  }

}
