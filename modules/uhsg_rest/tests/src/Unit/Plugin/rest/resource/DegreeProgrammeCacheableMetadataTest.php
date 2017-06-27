<?php

use Drupal\uhsg_rest\Plugin\rest\resource\DegreeProgrammeCacheableMetadata;

/**
 * @group uhsg
 */
class DegreeProgrammeCacheableMetadataTest extends PHPUnit_Framework_TestCase {

  /** @var DegreeProgrammeCacheableMetadata */
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
