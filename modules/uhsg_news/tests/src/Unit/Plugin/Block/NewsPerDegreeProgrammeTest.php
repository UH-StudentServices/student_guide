<?php

use Drupal\Tests\UnitTestCase;
use Drupal\uhsg_news\Plugin\Block\NewsPerDegreeProgramme;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @group uhsg
 */
class NewsPerDegreeProgrammeTest extends UnitTestCase {

  /** @var ContainerInterface */
  private $container;

  /** @var NewsPerDegreeProgramme */
  private $newsPerDegreeProgramme;

  public function setUp() {
    parent::setUp();

    $this->container = $this->prophesize(ContainerInterface::class);

    Drupal::setContainer($this->container->reveal());

    $this->newsPerDegreeProgramme = new NewsPerDegreeProgrammeTestDouble();
  }

  /**
   * @test
   */
  public function shouldUseActiveDegreeProgrammeAsCacheContext() {
    $this->assertContains('active_degree_programme', $this->newsPerDegreeProgramme->getCacheContexts());
  }
}

/**
 * Test double for overriding difficult to test methods.
 */
class NewsPerDegreeProgrammeTestDouble extends NewsPerDegreeProgramme {

  public function __construct(array $configuration = [], $plugin_id = NULL, $plugin_definition = NULL) {
    // Do nothing.
  }
}