<?php

namespace Drupal\uhsg_other_education_provider\Plugin\views\argument_default;

use Drupal\Core\Cache\CacheableDependencyInterface;
use Drupal\uhsg_other_education_provider\OtherEducationProviderService;
use Drupal\views\Plugin\views\argument_default\ArgumentDefaultPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * The other education provider argument default handler.
 *
 * @ingroup views_argument_default_plugins
 *
 * @ViewsArgumentDefault(
 *   id = "other_education_provider",
 *   title = @Translation("Taxonomy term ID from other education provider")
 * )
 */
class OtherEducationProvider extends ArgumentDefaultPluginBase implements CacheableDependencyInterface {

  /** @var \Drupal\uhsg_other_education_provider\OtherEducationProviderService*/
  protected $otherEducationProviderService;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, OtherEducationProviderService $otherEducationProviderService) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->otherEducationProviderService = $otherEducationProviderService;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('uhsg_other_education_provider.other_education_provider')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getArgument() {
    return $this->otherEducationProviderService->getId();
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
    return ['other_education_provider'];
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheMaxAge() {
    return 0;
  }

}
