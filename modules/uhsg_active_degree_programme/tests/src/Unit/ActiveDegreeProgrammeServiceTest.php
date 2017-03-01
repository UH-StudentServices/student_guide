<?php

use Drupal\Core\Cache\Context\CacheContextsManager;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\taxonomy\Entity\Term;
use Drupal\uhsg_active_degree_programme\ActiveDegreeProgrammeService;
use Prophecy\Argument;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * @group uhsg
 */
class ActiveDegreeProgrammeServiceTest extends PHPUnit_Framework_TestCase {

  const ACTIVE_DEGREE_PROGRAMME_ID = 123;

  /** @var AccountInterface */
  private $account;

  /** @var ActiveDegreeProgrammeService */
  private $activeDegreeProgrammeService;

  /** @var ContainerInterface */
  private $container;

  /** @var CacheContextsManager */
  private $cacheContextsManager;

  /** @var ParameterBag */
  private $cookies;

  /** @var EntityManagerInterface */
  private $entityManager;

  /** @var EntityRepositoryInterface */
  private $entityRepository;

  /** @var EntityStorageInterface */
  private $entityStorage;

  /** @var EntityTypeInterface */
  private $entityType;

  /** @var HeaderBag */
  private $headers;

  /** @var LanguageInterface */
  private $language;

  /** @var ModuleHandlerInterface */
  private $moduleHandler;

  /** @var Request */
  private $request;

  /** @var RequestStack */
  private $requestStack;

  /** @var Term */
  private $term;

  public function setUp() {
    parent::setUp();

    $this->account = $this->prophesize(AccountInterface::class);

    $this->cacheContextsManager = $this->prophesize(CacheContextsManager::class);
    $this->cacheContextsManager->assertValidTokens(Argument::any())->willReturn(TRUE);

    $this->entityRepository = $this->prophesize(EntityRepositoryInterface::class);

    $this->entityType = $this->prophesize(EntityTypeInterface::class);

    $this->language = $this->prophesize(LanguageInterface::class);
    $this->language->getId()->willReturn();

    $this->term = $this->prophesize(Term::class);
    $this->term->id()->willReturn(self::ACTIVE_DEGREE_PROGRAMME_ID);
    $this->term->getEntityType()->willReturn($this->entityType);
    $this->term->language()->willReturn($this->language);
    $this->term->uuid()->willReturn();
    $this->term->getEntityType()->willReturn($this->entityType->reveal());
    $this->term->getEntityTypeId()->willReturn();

    $this->entityStorage = $this->prophesize(EntityStorageInterface::class);
    $this->entityStorage->load(Argument::any())->willReturn($this->term);

    $this->entityManager = $this->prophesize(EntityManagerInterface::class);
    $this->entityManager->getStorage(Argument::any())->willReturn($this->entityStorage->reveal());
    $this->entityManager->getEntityTypeFromClass(Argument::any())->willReturn();

    $this->cookies = $this->prophesize(ParameterBag::class);
    $this->headers = $this->prophesize(HeaderBag::class);

    $this->request = $this->prophesize(Request::class);
    $this->request->cookies = $this->cookies;
    $this->request->headers = $this->headers;

    $this->requestStack = $this->prophesize(RequestStack::class);
    $this->requestStack->getCurrentRequest()->willReturn($this->request->reveal());

    $this->moduleHandler = $this->prophesize(ModuleHandlerInterface::class);
    $this->moduleHandler->invokeAll(Argument::any(), Argument::any())->willReturn([]);

    $this->container = $this->prophesize(ContainerInterface::class);
    $this->container->get('entity.manager')->willReturn($this->entityManager->reveal());
    $this->container->get('module_handler')->willReturn($this->moduleHandler);
    $this->container->get('cache_contexts_manager')->willReturn($this->cacheContextsManager->reveal());

    Drupal::setContainer($this->container->reveal());

    $this->activeDegreeProgrammeService = new ActiveDegreeProgrammeServiceTestDouble(
      $this->requestStack->reveal(),  $this->entityRepository->reveal(),  $this->account->reveal()
    );
  }

  /**
   * @test
   */
  public function getIdShouldGetActiveDegreeProgrammeIdFromQueryParameter() {
    $this->request->get('degree_programme')->willReturn(self::ACTIVE_DEGREE_PROGRAMME_ID);

    $this->assertEquals(self::ACTIVE_DEGREE_PROGRAMME_ID, $this->activeDegreeProgrammeService->getId());
  }

  /**
   * @test
   */
  public function getIdShouldGetActiveDegreeProgrammeIdFromRequestHeader() {
    $this->request->get('degree_programme')->willReturn(NULL);
    $this->headers->get('x-degree-programme')->willReturn(self::ACTIVE_DEGREE_PROGRAMME_ID);

    $this->assertEquals(self::ACTIVE_DEGREE_PROGRAMME_ID, $this->activeDegreeProgrammeService->getId());
  }

  /**
   * @test
   */
  public function getIdShouldGetActiveDegreeProgrammeIdFromCookie() {
    $this->request->get('degree_programme')->willReturn(NULL);
    $this->headers->get('x-degree-programme')->willReturn(NULL);
    $this->cookies->get('Drupal_visitor_degree_programme')->willReturn(self::ACTIVE_DEGREE_PROGRAMME_ID);

    $this->assertEquals(self::ACTIVE_DEGREE_PROGRAMME_ID, $this->activeDegreeProgrammeService->getId());
  }
}

/**
 * Test double for overwriting difficult-to-unit-test functionalities.
 */
class ActiveDegreeProgrammeServiceTestDouble extends ActiveDegreeProgrammeService {

  protected function access(Term $term) {
    return TRUE;
  }

  protected function debug($message) {
    // Do nothing.
  }

  protected function deleteCookie() {
    // Do nothing.
  }

  protected function saveCookie($cookie) {
    // Do nothing.
  }
}