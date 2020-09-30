<?php

namespace Drupal\uhsg_sitemap\Plugin\simple_sitemap\UrlGenerator;

use Drupal\simple_sitemap\EntityHelper;
use Drupal\simple_sitemap\Logger;
use Drupal\simple_sitemap\Plugin\simple_sitemap\UrlGenerator\EntityUrlGeneratorBase;
use Drupal\simple_sitemap\Plugin\simple_sitemap\UrlGenerator\UrlGeneratorManager;
use Drupal\simple_sitemap\Simplesitemap;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class GuideNodeUrlGenerator
 *
 * @UrlGenerator(
 *   id = "guide_node",
 *   label = @Translation("Guide node URL generator"),
 *   description = @Translation("Generates URLs for Guide nodes with support for degree programme selections."),
 * )
 */
class GuideNodeUrlGenerator extends EntityUrlGeneratorBase {

  /**
   * @var \Drupal\simple_sitemap\Plugin\simple_sitemap\UrlGenerator\UrlGeneratorManager
   */
  protected $urlGeneratorManager;

  /**
   * @var array
   */
  protected $degreeProgrammeTids;

  /**
   * EntityUrlGenerator constructor.
   * 
   * @param array $configuration
   * @param $plugin_id
   * @param $plugin_definition
   * @param \Drupal\simple_sitemap\Simplesitemap $generator
   * @param \Drupal\simple_sitemap\Logger $logger
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   * @param \Drupal\simple_sitemap\EntityHelper $entityHelper
   * @param \Drupal\simple_sitemap\Plugin\simple_sitemap\UrlGenerator\UrlGeneratorManager $url_generator_manager
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    Simplesitemap $generator,
    Logger $logger,
    LanguageManagerInterface $language_manager,
    EntityTypeManagerInterface $entity_type_manager,
    EntityHelper $entityHelper,
    UrlGeneratorManager $url_generator_manager
  ) {
    parent::__construct(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $generator,
      $logger,
      $language_manager,
      $entity_type_manager,
      $entityHelper
    );
    $this->urlGeneratorManager = $url_generator_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(
    ContainerInterface $container,
    array $configuration,
    $plugin_id,
    $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('simple_sitemap.generator'),
      $container->get('simple_sitemap.logger'),
      $container->get('language_manager'),
      $container->get('entity_type.manager'),
      $container->get('simple_sitemap.entity_helper'),
      $container->get('plugin.manager.simple_sitemap.url_generator')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getDataSets() {
    $data_sets = [];

    $bundle_settings = $this->generator
      ->setVariants($this->sitemapVariant)
      ->getBundleSettings();

    if (!empty($bundle_settings['node'])) {
      $nodeStorage = $this->entityTypeManager->getStorage('node');
      foreach ($bundle_settings['node'] as $bundle_name => $bundle_settings) {
        if (!empty($bundle_settings['index'])) {
          $query = $nodeStorage->getQuery();
          $query
            ->sort('nid', 'ASC')
            ->condition('type', $bundle_name)
            ->condition('status', 1);

          $query->accessCheck(FALSE);

          foreach ($query->execute() as $entity_id) {
            $data_sets[] = [
              'nid' => $entity_id,
              'degree_tid' => NULL,
            ];

            if ($degree_tids = $this->getDegreeProgrammeTids()) {
              foreach ($degree_tids as $tid) {
                $data_sets[] = [
                  'nid' => $entity_id,
                  'degree_tid' => $tid,
                ];
              }
            }
          }
        }
      }
    }

    return $data_sets;
  }

  /**
   * {@inheritdoc}
   */
  protected function processDataSet($data_set) {
    if (empty($entity = $this->entityTypeManager->getStorage('node')->load($data_set['nid']))) {
      return FALSE;
    }

    $entity_id = $entity->id();
    $entity_type_name = $entity->getEntityTypeId();

    $entity_settings = $this->generator
      ->setVariants($this->sitemapVariant)
      ->getEntityInstanceSettings($entity_type_name, $entity_id);

    if (empty($entity_settings['index'])) {
      return FALSE;
    }

    $url_object = $entity->toUrl();

    // Do not include external paths.
    if (!$url_object->isRouted()) {
      return FALSE;
    }

    // Add active degree programme query parameter if provided.
    if ($data_set['degree_tid']) {
      $url_object->setOption('query', [
        'degree_programme' => (int) $data_set['degree_tid'],
      ]);
    }

    $path = $url_object->getInternalPath();

    $url_object->setOption('absolute', TRUE);

    static $test = 0;
    if ($data_set['degree_tid'] && $test < 2) {
      $test++;
    }

    return [
      'url' => $url_object,
      'lastmod' => method_exists($entity, 'getChangedTime') ? date('c', $entity->getChangedTime()) : NULL,
      'priority' => isset($entity_settings['priority']) ? $entity_settings['priority'] : NULL,
      'changefreq' => !empty($entity_settings['changefreq']) ? $entity_settings['changefreq'] : NULL,
      'images' => !empty($entity_settings['include_images'])
        ? $this->getEntityImageData($entity)
        : [],

      // Additional info useful in hooks.
      // Excluding the 'path'-key here as it is used to remove duplicates
      // in Drupal\simple_sitemap\Queue::removeDuplicates(). We share the
      // same path with all degree programme tid variations so this path
      // based duplicate detection doesn't work for us here.
      'meta' => [
        'entity_info' => [
          'entity_type' => $entity_type_name,
          'id' => $entity_id,
          'degree_tid' => $data_set['degree_tid'] ?? NULL,
        ],
      ]
    ];
  }

  /**
   * Get all degree programme term id's.
   *
   * @return int[]
   *   Array of degree programme id's or an empty array if none were found.
   */
  protected function getDegreeProgrammeTids() {
    if (empty($this->degreeProgrammeTids)) {
      $query = $this->entityTypeManager->getStorage('taxonomy_term')->getQuery();
      $query
        ->sort('tid', 'ASC')
        ->condition('vid', 'degree_programme')
        ->condition('status', 1);
      
      $this->degreeProgrammeTids = $query->execute();
    }

    return $this->degreeProgrammeTids;
  }

}
