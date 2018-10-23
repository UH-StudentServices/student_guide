<?php

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\uhsg_active_degree_programme\ActiveDegreeProgrammeService;
use Drupal\uhsg_active_degree_programme\Cache\Context\ActiveDegreeProgrammeCacheContext;

/**
 * @group uhsg
 */
class ActiveDegreeProgrammeCacheContextTest extends PHPUnit_Framework_TestCase {

  const ACTIVE_DEGREE_PROGRAMME = 123;

  /** @var \Drupal\uhsg_active_degree_programme\Cache\Context\ActiveDegreeProgrammeCacheContext*/
  private $activeDegreeProgrammeCacheContext;

  /** @var \Drupal\uhsg_active_degree_programme\ActiveDegreeProgrammeService*/
  private $activeDegreeProgrammeService;

  public function setUp() {
    parent::setUp();

    $this->activeDegreeProgrammeService = $this->prophesize(ActiveDegreeProgrammeService::class);

    $this->activeDegreeProgrammeCacheContext = new ActiveDegreeProgrammeCacheContext($this->activeDegreeProgrammeService->reveal());
  }

  /**
   * @test
   */
  public function getContextShouldReturnActiveDegreeProgrammeWhenActiveDegreeProgrammeExists() {
    $this->activeDegreeProgrammeService->getId()->willReturn(self::ACTIVE_DEGREE_PROGRAMME);

    $this->assertEquals(self::ACTIVE_DEGREE_PROGRAMME, $this->activeDegreeProgrammeCacheContext->getContext());
  }

  /**
   * @test
   */
  public function getContextShouldReturnZeroWhenActiveDegreeProgrammeDoesNotExist() {
    $this->activeDegreeProgrammeService->getId()->willReturn();

    $this->assertEquals(0, $this->activeDegreeProgrammeCacheContext->getContext());
  }

  /**
   * @test
   */
  public function shouldAllowCacheOptimizationByReturningEmptyCacheableMetadata() {
    $this->assertEquals(new CacheableMetadata(), $this->activeDegreeProgrammeCacheContext->getCacheableMetadata());
  }

}
