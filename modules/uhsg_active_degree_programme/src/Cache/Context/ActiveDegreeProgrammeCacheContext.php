<?php

namespace Drupal\uhsg_active_degree_programme\Cache\Context;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Cache\Context\CacheContextInterface;
use Drupal\uhsg_active_degree_programme\ActiveDegreeProgrammeService;

/**
 * Defines the ActiveDegreeProgrammeCacheContext service, for "per degree
 * programme" caching.
 *
 * Cache context ID: 'active_degree_programme'.
 */
class ActiveDegreeProgrammeCacheContext implements CacheContextInterface {

  protected $activeDegreeProgrammeService;

  public function __construct(ActiveDegreeProgrammeService $activeDegreeProgrammeService) {
    $this->activeDegreeProgrammeService = $activeDegreeProgrammeService;
  }

  /**
   * {@inheritdoc}
   */
  public static function getLabel() {
    return t('Active Degree Programme');
  }

  /**
   * {@inheritdoc}
   */
  public function getContext() {
    $activeId = $this->activeDegreeProgrammeService->getId();
    return $activeId ? $activeId : 0;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheableMetadata() {
    return new CacheableMetadata();
  }

}
