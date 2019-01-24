<?php

use Drupal\Core\Access\AccessResultAllowed;
use Drupal\Core\Access\AccessResultForbidden;
use Drupal\Core\Cache\Context\CacheContextsManager;
use Drupal\Core\Session\AccountInterface;
use Drupal\Tests\UnitTestCase;
use Drupal\uhsg_active_degree_programme\ActiveDegreeProgrammeService;
use Drupal\uhsg_domain\DomainService;
use Drupal\uhsg_news\Plugin\Block\NewsPerDegreeProgramme;
use Prophecy\Argument;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @group uhsg
 */
class NewsPerDegreeProgrammeTest extends UnitTestCase {

  /** @var AccountInterface*/
  private $account;

  /** @var \Drupal\uhsg_active_degree_programme\ActiveDegreeProgrammeService*/
  private $activeDegreeProgrammeService;

  /** @var \Drupal\Core\Cache\Context\CacheContextsManager*/
  private $cacheContextsManager;

  /** @var \Symfony\Component\DependencyInjection\ContainerInterface*/
  private $container;

  /** @var DomainService*/
  private $domainService;

  /** @var \Drupal\uhsg_news\Plugin\Block\NewsPerDegreeProgramme*/
  private $newsPerDegreeProgramme;

  public function setUp() {
    parent::setUp();

    $this->account = $this->prophesize(AccountInterface::class);
    $this->activeDegreeProgrammeService = $this->prophesize(ActiveDegreeProgrammeService::class);

    $this->cacheContextsManager = $this->prophesize(CacheContextsManager::class);
    $this->cacheContextsManager->assertValidTokens(Argument::any())->willReturn(TRUE);

    $this->domainService = $this->prophesize(DomainService::class);

    $this->container = $this->prophesize(ContainerInterface::class);
    $this->container->get('cache_contexts_manager')->willReturn($this->cacheContextsManager->reveal());
    $this->container->get('uhsg_active_degree_programme.active_degree_programme')->willReturn($this->activeDegreeProgrammeService->reveal());
    $this->container->get('uhsg_domain.domain')->willReturn($this->domainService->reveal());

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

  /**
   * @test
   */
  public function shouldAllowAccessOnStudentDomain() {
    $this->domainService->isStudentDomain()->willReturn(TRUE);

    $this->assertInstanceOf(AccessResultAllowed::class, $this->newsPerDegreeProgramme->access($this->account->reveal()));
  }

  /**
   * @test
   */
  public function shouldForbidAccessWhenNotStudentDomain() {
    $this->domainService->isStudentDomain()->willReturn(FALSE);

    $this->assertInstanceOf(AccessResultForbidden::class, $this->newsPerDegreeProgramme->access($this->account->reveal()));
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
