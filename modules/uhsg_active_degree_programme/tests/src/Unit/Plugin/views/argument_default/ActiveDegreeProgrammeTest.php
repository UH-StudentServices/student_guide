<?php

use Drupal\Tests\UnitTestCase;
use Drupal\uhsg_active_degree_programme\ActiveDegreeProgrammeService;
use Drupal\uhsg_active_degree_programme\Plugin\views\argument_default\ActiveDegreeProgramme;

/**
 * @group uhsg
 */
class ActiveDegreeProgrammeTest extends UnitTestCase {

  const ACTIVE_DEGREE_PROGRAMME_ID = 123;

  /** @var \Drupal\uhsg_active_degree_programme\Plugin\views\argument_default\ActiveDegreeProgramme*/
  private $activeDegreeProgramme;

  /** @var \Drupal\uhsg_active_degree_programme\ActiveDegreeProgrammeService*/
  private $activeDegreeProgrammeService;

  public function setUp() {
    $this->activeDegreeProgrammeService = $this->prophesize(ActiveDegreeProgrammeService::class);
    $this->activeDegreeProgrammeService->getId()->willReturn(self::ACTIVE_DEGREE_PROGRAMME_ID);

    $this->activeDegreeProgramme = new ActiveDegreeProgramme([], NULL, [], $this->activeDegreeProgrammeService->reveal());
  }

  /**
   * @test
   */
  public function shouldNotGetCached() {
    $this->assertEquals(0, $this->activeDegreeProgramme->getCacheMaxAge());
  }

  /**
   * @test
   */
  public function shouldHaveActiveDegreeProgrammeAsCacheContext() {
    $this->assertEquals(['active_degree_programme'], $this->activeDegreeProgramme->getCacheContexts());
  }

  /**
   * @test
   */
  public function shouldReturnActiveDegreeProgrammeIdAsDefaultArgument() {
    $this->assertEquals(self::ACTIVE_DEGREE_PROGRAMME_ID, $this->activeDegreeProgramme->getArgument());
  }

}
