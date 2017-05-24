<?php

namespace Drupal\uhsg_rest\Plugin\rest\resource;

use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Language\LanguageInterface;
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

  /** @var EntityRepositoryInterface */
  protected $entityRepository;

  /** @var EntityTypeManagerInterface */
  protected $entityTypeManager;

  /** @var LanguageManagerInterface */
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
   * @return TermInterface[]
   */
  private function loadAllDegreeProgrammeTerms() {
    return $this->entityTypeManager->getStorage('taxonomy_term')->loadTree('degree_programme', 0, NULL, TRUE);
  }

  /**
   * @param TermInterface[] $degreeProgrammeTerms
   * @return array
   */
  private function formatDegreeProgrammeTermsForResponse($degreeProgrammeTerms) {
    foreach ($degreeProgrammeTerms as $term) {
      $code = $term->get('field_code')->value;
      $name = $this->getNameTranslations($term);
      $degreeProgrammes[] = ['code' => $code, 'name' => $name];
    }

    return isset($degreeProgrammes) ? $degreeProgrammes : [];
  }

  /**
   * @param TermInterface $term
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

}
