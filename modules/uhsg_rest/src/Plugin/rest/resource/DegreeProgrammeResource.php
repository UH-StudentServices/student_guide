<?php

namespace Drupal\uhsg_rest\Plugin\rest\resource;

use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Drupal\taxonomy\TermInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a resource for degree programme taxonomy terms.
 *
 * @RestResource(
 *   id = "degree_programme",
 *   label = @Translation("Degree programme"),
 *   uri_paths = {
 *     "canonical" = "/api/v1/degree-programme"
 *   }
 * )
 */
class DegreeProgrammeResource extends ResourceBase {

  /** @var \Drupal\Core\Entity\EntityRepositoryInterface*/
  protected $entityRepository;

  /** @var \Drupal\Core\Entity\EntityTypeManagerInterface*/
  protected $entityTypeManager;

  /** @var \Drupal\Core\Language\LanguageManagerInterface*/
  protected $languageManager;

  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    array $serializer_formats,
    LoggerInterface $logger,
    EntityRepositoryInterface $entityRepository,
    EntityTypeManagerInterface $entityTypeManager,
    LanguageManagerInterface $languageManager) {

    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);
    $this->entityRepository = $entityRepository;
    $this->entityTypeManager = $entityTypeManager;
    $this->languageManager = $languageManager;
  }

  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->getParameter('serializer.formats'),
      $container->get('logger.factory')->get('rest'),
      $container->get('entity.repository'),
      $container->get('entity_type.manager'),
      $container->get('language_manager')
    );
  }

  /**
   * Responds to GET requests. Returns all degree programmes.
   *
   * @return \Drupal\rest\ResourceResponse
   *   The response containing the degree programmes.
   */
  public function get() {
    $degreeProgrammeTerms = $this->loadAllDegreeProgrammeTerms();
    $degreeProgrammeResponse = $this->formatDegreeProgrammeTermsForResponse($degreeProgrammeTerms);
    $response = new ResourceResponse($degreeProgrammeResponse);
    $response->addCacheableDependency(new DegreeProgrammeCacheableMetadata());

    foreach ($degreeProgrammeTerms as $degreeProgrammeTerm) {
      $response->addCacheableDependency($degreeProgrammeTerm);
    }

    return $response;
  }

  /**
   * @return \Drupal\taxonomy\TermInterface[]
   */
  private function loadAllDegreeProgrammeTerms() {
    return $this->entityTypeManager->getStorage('taxonomy_term')->loadTree('degree_programme', 0, NULL, TRUE);
  }

  /**
   * @param \Drupal\taxonomy\TermInterface[] $degreeProgrammeTerms
   * @return array
   */
  private function formatDegreeProgrammeTermsForResponse($degreeProgrammeTerms) {
    foreach ($degreeProgrammeTerms as $term) {
      $code = $term->get('field_code')->value;
      $programme_type = $term->get('field_degree_programme_type')->value;
      $name = $this->getNameTranslations($term);
      // $degreeProgrammes[] = ['code' => $code, 'name' => $name];
      // $this->getNodeCount() caused fatal errors on jsonapi anon page load,
      // which is rather complex and misleading. In this case it's easiest
      // to limit by removing access perm checks, which is ok in this case
      // because its really only fetching a number.
      //
      // The problems complexity is best described by Lullabot:
      // https://www.lullabot.com/articles/early-rendering-a-lesson-in-debugging-drupal-8
      // However the simple access check fix is based on this thread:
      // https://drupal.stackexchange.com/questions/251864/logicexception-the-controller-result-claims-to-be-providing-relevant-cache-meta
      $newsCount = $this->getNodeCount($term, "news");
      $contentCount = [
        'news' => $newsCount,
      ];

      $degreeProgrammes[] = [
        'code' => $code,
        'programme_type' => $programme_type,
        'contentCount' => $contentCount,
        'name' => $name
      ];
    }

    return isset($degreeProgrammes) ? $degreeProgrammes : [];
  }

  /**
   * @param \Drupal\taxonomy\TermInterface $term
   * @return string[]
   */
  private function getNameTranslations(TermInterface $term) {
    $nameTranslations = [];
    $languages = $this->languageManager->getLanguages();

    $languageCodes = array_map(function ($language) {
      /** @var $language LanguageInterface */
      return $language->getId();
    }, $languages);

    foreach ($languageCodes as $languageCode) {
      $name = $this->entityRepository->getTranslationFromContext($term, $languageCode)->label();
      $nameTranslations[$languageCode] = $name;
    }

    return $nameTranslations;
  }

  /**
   * @param \Drupal\taxonomy\TermInterface $term
   * @param string content-type
   * @return int
   */
  private function getNodeCount(TermInterface $term, $content_type) {
    $contentCount = null;

    $languages = $this->languageManager->getLanguages();
    $languageCodes = array_map(function ($language) {
      /** @var $language LanguageInterface */
      return $language->getId();
    }, $languages);

    foreach ($languageCodes as $languageCode) {
      $nodes[$languageCode] = \Drupal::entityTypeManager()->getStorage('node')->getQuery()
        ->condition('field_news_degree_programme', $term->tid->value)
        ->condition('type', $content_type)
        ->condition('langcode', $languageCode)
        ->condition('status', 1)
        ->accessCheck(false)
        ->execute();

        if(count($nodes[$languageCode]) > 0) {
          // Return count, not actual nodes.
          $contentCount[$languageCode] = count($nodes[$languageCode]);
        }
    }

    return $contentCount;
  }
}
