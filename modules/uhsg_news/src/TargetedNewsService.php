<?php

namespace Drupal\uhsg_news;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\uhsg_active_degree_programme\ActiveDegreeProgrammeService;

class TargetedNewsService {

  /**
   * For querying IDs of news.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * For getting active language and therefore correct language of values.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * For getting correct targeter.
   *
   * @var \Drupal\uhsg_active_degree_programme\ActiveDegreeProgrammeService
   */
  protected $activeDegreeProgrammeService;

  protected $targetEntityType = 'node';
  protected $targetBundle = 'news';
  protected $referenceField = 'field_news_degree_programme';

  /**
   * @param EntityTypeManagerInterface $entityTypeManager
   * @param LanguageManagerInterface $languageManager
   * @param ActiveDegreeProgrammeService $activeDegreeProgrammeService
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager, LanguageManagerInterface $languageManager, ActiveDegreeProgrammeService $activeDegreeProgrammeService) {
    $this->entityTypeManager = $entityTypeManager;
    $this->languageManager = $languageManager;
    $this->activeDegreeProgrammeService = $activeDegreeProgrammeService;
  }

  /**
   * Returns collection of Node IDs that is targeted for current request.
   *
   * @param int $limit
   *   Number of maximum items to fetch.
   *
   * @return array
   */
  public function getTargetedNewsNids($limit = 4) {
    $query = \Drupal::entityQuery($this->targetEntityType)
      ->condition('status', NODE_PUBLISHED)
      ->condition('langcode', $this->languageManager->getCurrentLanguage()->getId())
      ->condition('type', $this->targetBundle)
      ->sort('created', 'DESC')
      ->range(0, $limit);
    $group = $query->orConditionGroup()
      ->condition($this->referenceField, NULL, 'IS NULL');

    // If active degree programme present, add tid to condition group
    $tid = $this->activeDegreeProgrammeService->getId();
    if ($tid) {
      $group->condition($this->referenceField, $tid);
    }

    $nids = $query->condition($group)->execute();
    return !empty($nids) ? $nids : array();
  }

}
