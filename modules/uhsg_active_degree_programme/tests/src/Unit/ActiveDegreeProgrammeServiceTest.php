<?php

use Drupal\Core\Cache\Context\CacheContextsManager;
use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\EntityTypeRepositoryInterface;
use Drupal\Core\Entity\Query\QueryInterface;
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

  const ACCOUNT_ID = 123;
  const ACTIVE_DEGREE_PROGRAMME_ID = 123;
  const DEGREE_PROGRAMME_BUNDLE = 'degree_programme';
  const PRIMARY_FIELD_NAME = 'Primary field name';

  /** @var \Drupal\Core\Session\AccountInterface*/
  private $account;

  /** @var \Drupal\uhsg_active_degree_programme\ActiveDegreeProgrammeService*/
  private $activeDegreeProgrammeService;

  /** @var \Drupal\Core\Config\ConfigFactory*/
  private $configFactory;

  /** @var \Symfony\Component\DependencyInjection\ContainerInterface*/
  private $container;

  /** @var \Drupal\Core\Cache\Context\CacheContextsManager*/
  private $cacheContextsManager;

  /** @var \Drupal\Core\Config\ImmutableConfig*/
  private $config;

  /** @var \Symfony\Component\HttpFoundation\ParameterBag*/
  private $cookies;

  /** @var \Drupal\Core\Entity\EntityRepositoryInterface*/
  private $entityRepository;

  /** @var \Drupal\Core\Entity\EntityStorageInterface*/
  private $entityStorage;

  /** @var \Drupal\Core\Entity\EntityTypeInterface*/
  private $entityType;

  /** @var \Drupal\Core\Entity\EntityTypeManagerInterface*/
  private $entityTypeManager;

  /** @var \Drupal\Core\Entity\EntityTypeRepositoryInterface*/
  private $entityTypeRepository;

  /** @var \Drupal\Core\Field\FieldItemListInterface*/
  private $fieldItemList;

  /** @var \Drupal\flag\FlagInterface*/
  private $flag;

  /** @var \Drupal\flag\FlaggingInterface*/
  private $flagging;

  /** @var \Drupal\flag\FlagService*/
  private $flagService;

  /** @var \Symfony\Component\HttpFoundation\HeaderBag*/
  private $headers;

  /** @var \Drupal\Core\Language\LanguageInterface*/
  private $language;

  /** @var \Drupal\Core\Extension\ModuleHandlerInterface*/
  private $moduleHandler;

  /** @var \Drupal\Core\Entity\Query\QueryInterface*/
  private $query;

  /** @var \Symfony\Component\HttpFoundation\Request*/
  private $request;

  /** @var \Symfony\Component\HttpFoundation\RequestStack*/
  private $requestStack;

  /** @var \Drupal\taxonomy\Entity\Term*/
  private $term;

  /** @var \Drupal\Core\TypedData\TypedDataInterface*/
  private $typedData;

  public function setUp() {
    parent::setUp();

    $this->account = $this->prophesize(AccountInterface::class);
    $this->account->id()->willReturn(self::ACCOUNT_ID);
    $this->account->isAnonymous()->willReturn(FALSE);
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

    $this->query = $this->prophesize(QueryInterface::class);

    $this->typedData = $this->prophesize(TypedDataInterface::class);
    $this->typedData->getValue()->willReturn(['value' => 'value']);

    $this->fieldItemList = $this->prophesize(FieldItemListInterface::class);
    $this->fieldItemList->first()->willReturn($this->typedData);
    $this->fieldItemList->isEmpty()->willReturn(FALSE);

    $this->flagging = $this->prophesize(FlaggingInterface::class);
    $this->flagging->hasField(self::PRIMARY_FIELD_NAME)->willReturn(TRUE);
    $this->flagging->get(self::PRIMARY_FIELD_NAME)->willReturn($this->fieldItemList);
    $this->flagging->getFlaggable()->willReturn($this->term);

    $this->entityStorage = $this->prophesize(EntityStorageInterface::class);
    $this->entityStorage->load(Argument::any())->willReturn($this->term);
    $this->entityStorage->loadMultiple(Argument::any())->willReturn([$this->flagging]);
    $this->entityStorage->getQuery(Argument::any())->willReturn($this->query);

    $this->entityTypeManager = $this->prophesize(EntityTypeManagerInterface::class);
    $this->entityTypeManager->getStorage(Argument::any())->willReturn($this->entityStorage);

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

    $this->flagService = $this->prophesize(FlagService::class);
    $this->flagService->getFlagById(Argument::any())->willReturn($this->flag);

    $this->requestStack = $this->prophesize(RequestStack::class);
    $this->requestStack->getCurrentRequest()->willReturn($this->request->reveal());

    $this->moduleHandler = $this->prophesize(ModuleHandlerInterface::class);
    $this->moduleHandler->invokeAll(Argument::any(), Argument::any())->willReturn([]);

    $this->entityTypeRepository = $this->prophesize(EntityTypeRepositoryInterface::class);
    $this->entityTypeRepository->getEntityTypeFromClass(Argument::any())->willReturnArgument(0);

    $this->container = $this->prophesize(ContainerInterface::class);
    $this->container->get('entity_type.manager')->willReturn($this->entityTypeManager);
    $this->container->get('entity_type.repository')->willReturn($this->entityTypeRepository);
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
