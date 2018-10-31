<?php

namespace Drupal\uhsg_active_degree_programme\Plugin\views\argument_default;

use Drupal\Core\Cache\CacheableDependencyInterface;
use Drupal\uhsg_active_degree_programme\ActiveDegreeProgrammeService;
use Drupal\views\Plugin\views\argument_default\ArgumentDefaultPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

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

  /** @var \Drupal\uhsg_active_degree_programme\ActiveDegreeProgrammeService*/
  protected $activeDegreeProgrammeService;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ActiveDegreeProgrammeService $activeDegreeProgrammeService) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->activeDegreeProgrammeService = $activeDegreeProgrammeService;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('uhsg_active_degree_programme.active_degree_programme')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getArgument() {
    return $this->activeDegreeProgrammeService->getId();
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
