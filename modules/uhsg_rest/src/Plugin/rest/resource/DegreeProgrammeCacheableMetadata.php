<?php

namespace Drupal\uhsg_rest\Plugin\rest\resource;

use Drupal\Core\Cache\CacheableDependencyInterface;

class DegreeProgrammeCacheableMetadata implements CacheableDependencyInterface {

  public function getCacheContexts() {
    return [];
  }

  public function getCacheTags() {
    return ['taxonomy_term_list'];
  }

  public function getCacheMaxAge() {
    return 0;
  }
}
