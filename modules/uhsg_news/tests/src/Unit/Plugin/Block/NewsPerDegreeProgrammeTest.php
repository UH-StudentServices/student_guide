<?php

use Drupal\Core\Cache\Context\CacheContextsManager;
use Drupal\Tests\UnitTestCase;
use Drupal\uhsg_active_degree_programme\ActiveDegreeProgrammeService;
use Drupal\uhsg_news\Plugin\Block\NewsPerDegreeProgramme;
use Prophecy\Argument;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @group uhsg
 */
class NewsPerDegreeProgrammeTest extends UnitTestCase {

  /** @var \Drupal\uhsg_active_degree_programme\ActiveDegreeProgrammeService*/
  private $activeDegreeProgrammeService;

  /** @var \Drupal\Core\Cache\Context\CacheContextsManager*/
  private $cacheContextsManager;

  /** @var \Symfony\Component\DependencyInjection\ContainerInterface*/
  private $container;

  /** @var \Drupal\uhsg_news\Plugin\Block\NewsPerDegreeProgramme*/
  private $newsPerDegreeProgramme;

  public function setUp() {
    parent::setUp();

    $this->activeDegreeProgrammeService = $this->prophesize(ActiveDegreeProgrammeService::class);

    $this->cacheContextsManager = $this->prophesize(CacheContextsManager::class);
    $this->cacheContextsManager->assertValidTokens(Argument::any())->willReturn(TRUE);

    $this->container = $this->prophesize(ContainerInterface::class);
    $this->container->get('cache_contexts_manager')->willReturn($this->cacheContextsManager->reveal());
    $this->container->get('uhsg_active_degree_programme.active_degree_programme')->willReturn($this->activeDegreeProgrammeService->reveal());

    Drupal::setContainer($this->container->reveal());

    $this->newsPerDegreeProgramme = new NewsPerDegreeProgrammeTestDouble();
  }

  /**
   * @test
   */
  public function shouldUseActiveDegreeProgrammeAsCacheContext() {
    $this->assertContains('active_degree_programme', $this->newsPerDegreeProgramme->getCacheContexts());
  }

  /**
   * @test
   */
  public function buildShouldReturnEmptyRenderableWhenThereIsNoActiveDegreeProgramme() {
    $this->activeDegreeProgrammeService->getId()->willReturn(NULL);

    $this->assertEquals([], $this->newsPerDegreeProgramme->build());
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
