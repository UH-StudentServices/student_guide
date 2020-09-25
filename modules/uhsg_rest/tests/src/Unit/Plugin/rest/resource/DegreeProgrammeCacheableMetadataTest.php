<?php

use Drupal\uhsg_rest\Plugin\rest\resource\DegreeProgrammeCacheableMetadata;
use PHPUnit\Framework\TestCase;

/**
 * @group uhsg
 */
class DegreeProgrammeCacheableMetadataTest extends TestCase {

  /** @var \Drupal\uhsg_rest\Plugin\rest\resource\DegreeProgrammeCacheableMetadata*/
  private $degreeProgrammeCacheableMetadata;

  public function setUp() {
    parent::setUp();

    $this->degreeProgrammeCacheableMetadata = new DegreeProgrammeCacheableMetadata();
  }

  /**
   * @test
   */
  public function cacheTagsShouldReturnTaxonomyTermList() {
    $this->assertEquals(['taxonomy_term_list'], $this->degreeProgrammeCacheableMetadata->getCacheTags());
  }

}
