<?php

namespace Drupal\uhsg_other_education_provider\Cache\Context;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Cache\Context\CacheContextInterface;
use Drupal\uhsg_other_education_provider\OtherEducationProviderService;

/**
 * Defines the OtherEducationProviderCacheContext service, for "per other
 * education provider" caching.
 *
 * Cache context ID: 'other_education_provider'.
 */
class OtherEducationProviderCacheContext implements CacheContextInterface {

  protected $otherEducationProviderService;

  public function __construct(OtherEducationProviderService $otherEducationProviderService) {
    $this->otherEducationProviderService = $otherEducationProviderService;
  }

  /**
   * {@inheritdoc}
   */
  public static function getLabel() {
    return t('Other education provider');
  }

  /**
   * {@inheritdoc}
   */
  public function getContext() {
    $active = $this->otherEducationProviderService->getId();
    return $active ? $active : 0;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheableMetadata() {
    return new CacheableMetadata();
  }

}
