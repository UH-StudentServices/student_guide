<?php

namespace Drupal\uhsg_news;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\Query\QueryInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\node\NodeInterface;
use Drupal\uhsg_active_degree_programme\ActiveDegreeProgrammeService;

class NewsService {

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
   * Get News node IDs for the given amount of news (both general and programme
   * specific).
   *
   * @param int $limit Number of maximum items to fetch.
   *
   * @return array
   */
  public function getNewsNids($limit = 4) {
    $query = $this->getBaseQuery($limit);
    $group = $query->orConditionGroup()->condition($this->referenceField, NULL, 'IS NULL');

    // If active degree programme is present, add term ID to condition group.
    $tid = $this->activeDegreeProgrammeService->getId();

    if ($tid) {
      $group->condition($this->referenceField, $tid);
    }

    $nids = $query->condition($group)->execute();

    return empty($nids) ? [] : $nids;
  }

  /**
   * Get News node IDs for the given amount of general news.
   *
   * @param int $limit Number of maximum items to fetch.
   *
   * @return array
   */
  public function getGeneralNewsNids($limit = 4) {
    $query = $this->getBaseQuery($limit)->condition($this->referenceField, NULL, 'IS NULL');
    $nids = $query->execute();

    return empty($nids) ? [] : $nids;
  }

  /**
   * Get News node IDs for the given amount of news (programme specific).
   *
   * @param int $limit Number of maximum items to fetch.
   *
   * @return array
   */
  public function getProgrammeNewsNids($limit = 4) {
    $query = $this->getBaseQuery($limit)
      ->condition($this->referenceField, NULL, 'IS NOT NULL')
      ->condition($this->referenceField, $this->activeDegreeProgrammeService->getId());
    $nids = $query->execute();

    return empty($nids) ? [] : $nids;
  }

  /**
   * @param int $limit
   * @return QueryInterface
   */
  private function getBaseQuery($limit) {
    return \Drupal::entityQuery($this->targetEntityType)
      ->condition('status', NodeInterface::PUBLISHED)
      ->condition('langcode', $this->languageManager->getCurrentLanguage()->getId())
      ->condition('type', $this->targetBundle)
      ->sort('created', 'DESC')
      ->range(0, $limit);
  }
}
