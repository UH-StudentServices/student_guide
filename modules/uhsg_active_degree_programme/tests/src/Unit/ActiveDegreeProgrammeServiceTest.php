<?php

use Drupal\Core\Cache\Context\CacheContextsManager;
use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\TypedData\TypedDataInterface;
use Drupal\flag\FlaggingInterface;
use Drupal\flag\FlagInterface;
use Drupal\flag\FlagService;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\taxonomy\Entity\Term;
use Drupal\Tests\UnitTestCase;
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
class ActiveDegreeProgrammeServiceTest extends UnitTestCase {

  const ACTIVE_DEGREE_PROGRAMME_ID = 123;
  const DEGREE_PROGRAMME_BUNDLE = 'degree_programme';
  const PRIMARY_FIELD_NAME = 'Primary field name';

  /** @var AccountInterface */
  private $account;

  /** @var ActiveDegreeProgrammeService */
  private $activeDegreeProgrammeService;

  /** @var ConfigFactory */
  private $configFactory;

  /** @var ContainerInterface */
  private $container;

  /** @var CacheContextsManager */
  private $cacheContextsManager;

  /** @var ImmutableConfig */
  private $config;

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

  /** @var EntityTypeManagerInterface */
  private $entityTypeManager;

  /** @var FieldItemListInterface */
  private $fieldItemList;

  /** @var FlagInterface */
  private $flag;

  /** @var FlaggingInterface */
  private $flagging;

  /** @var FlagService */
  private $flagService;

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

  /** @var TypedDataInterface */
  private $typedData;

  public function setUp() {
    parent::setUp();

    $this->account = $this->prophesize(AccountInterface::class);
    $this->account->isAuthenticated()->willReturn(TRUE);

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
    $this->term->getEntityTypeId()->willReturn('taxonomy_term');
    $this->term->getVocabularyId()->willReturn(self::DEGREE_PROGRAMME_BUNDLE);

    $this->entityStorage = $this->prophesize(EntityStorageInterface::class);
    $this->entityStorage->load(Argument::any())->willReturn($this->term);

    $this->entityManager = $this->prophesize(EntityManagerInterface::class);
    $this->entityManager->getStorage(Argument::any())->willReturn($this->entityStorage->reveal());
    $this->entityManager->getEntityTypeFromClass(Argument::any())->willReturn();

    $this->entityTypeManager = $this->prophesize(EntityTypeManagerInterface::class);

    $this->cookies = $this->prophesize(ParameterBag::class);
    $this->headers = $this->prophesize(HeaderBag::class);

    $this->request = $this->prophesize(Request::class);
    $this->request->cookies = $this->cookies;
    $this->request->headers = $this->headers;

    $this->config = $this->prophesize(ImmutableConfig::class);
    $this->config->get('primary_field_name')->willReturn(self::PRIMARY_FIELD_NAME);

    $this->configFactory = $this->prophesize(ConfigFactory::class);
    $this->configFactory->get('uhsg_active_degree_programme.settings')->willReturn($this->config);

    $this->flag = $this->prophesize(FlagInterface::class);

    $this->typedData = $this->prophesize(TypedDataInterface::class);
    $this->typedData->getValue()->willReturn(['value' => 'value']);

    $this->fieldItemList = $this->prophesize(FieldItemListInterface::class);
    $this->fieldItemList->first()->willReturn($this->typedData);
    $this->fieldItemList->isEmpty()->willReturn(FALSE);

    $this->flagging = $this->prophesize(FlaggingInterface::class);
    $this->flagging->hasField(self::PRIMARY_FIELD_NAME)->willReturn(TRUE);
    $this->flagging->get(self::PRIMARY_FIELD_NAME)->willReturn($this->fieldItemList);
    $this->flagging->getFlaggable()->willReturn($this->term);

    $this->flagService = $this->prophesize(FlagService::class);
    $this->flagService->getFlagById(Argument::any())->willReturn($this->flag);
    $this->flagService->getFlagFlaggings(Argument::any(), Argument::any())->willReturn([$this->flagging]);

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
      $this->configFactory->reveal(),
      $this->requestStack->reveal(),
      $this->entityRepository->reveal(),
      $this->entityTypeManager->reveal(),
      $this->account->reveal(),
      $this->flagService->reveal()
    );
  }

  /**
   * @test
   */
  public function getIdShouldGetActiveDegreeProgrammeIdFromQueryParameter() {
    $this->request->get('degree_programme')->willReturn(self::ACTIVE_DEGREE_PROGRAMME_ID);
    $this->request->get('degree_programme_code')->willReturn(NULL);

    $this->assertEquals(self::ACTIVE_DEGREE_PROGRAMME_ID, $this->activeDegreeProgrammeService->getId());
  }

  /**
   * @test
   */
  public function getIdShouldGetActiveDegreeProgrammeIdFromRequestHeader() {
    $this->request->get('degree_programme')->willReturn(NULL);
    $this->request->get('degree_programme_code')->willReturn(NULL);
    $this->headers->get('x-degree-programme')->willReturn(self::ACTIVE_DEGREE_PROGRAMME_ID);

    $this->assertEquals(self::ACTIVE_DEGREE_PROGRAMME_ID, $this->activeDegreeProgrammeService->getId());
  }

  /**
   * @test
   */
  public function getIdShouldGetActiveDegreeProgrammeIdFromCookie() {
    $this->request->get('degree_programme')->willReturn(NULL);
    $this->request->get('degree_programme_code')->willReturn(NULL);
    $this->headers->get('x-degree-programme')->willReturn(NULL);
    $this->cookies->get('Drupal_visitor_degree_programme')->willReturn(self::ACTIVE_DEGREE_PROGRAMME_ID);

    $this->assertEquals(self::ACTIVE_DEGREE_PROGRAMME_ID, $this->activeDegreeProgrammeService->getId());
  }

  /**
   * @test
   */
  public function getIdShouldGetActiveDegreeProgrammeIdFromFlaggings() {
    $this->request->get('degree_programme')->willReturn(NULL);
    $this->request->get('degree_programme_code')->willReturn(NULL);
    $this->headers->get('x-degree-programme')->willReturn(NULL);
    $this->cookies->get('Drupal_visitor_degree_programme')->willReturn(NULL);
    $this->term->id()->willReturn(self::ACTIVE_DEGREE_PROGRAMME_ID);

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