<?php

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Logger\LoggerChannel;
use Drupal\flag\FlagServiceInterface;
use Drupal\samlauth\Event\SamlAuthEvents;
use Drupal\samlauth\Event\SamlAuthUserSyncEvent;
use Drupal\Tests\UnitTestCase;
use Drupal\uhsg_oprek\Oprek\OprekServiceInterface;
use Drupal\uhsg_sisu\Services\StudyRightsServiceInterface;
use Drupal\uhsg_user_sync\SamlAuth\UserSyncSubscriber;
use Drupal\user\UserInterface;
use Drupal\Core\Session\AccountInterface;
use Prophecy\Argument;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Messenger\MessengerInterface;

/**
 * @group uhsg
 */
class UserSynscSubscriberTest extends UnitTestCase {

  const ATTRIBUTES = ['studentId'];
  const TECHNICAL_CONDITION_FIELD_NAME = 'technical_condition_field_name';
  const COMMON_NAME_FIELD_CONFIG_KEY = 'common_name_field_name';
  const COMMON_NAME_FIELD_CONFIG_VALUE = 'field_common_name';
  const CODE_FIELD_NAME = 'code_field_name';
  const HYPERSONID_FIELD_CONFIG_KEY = 'hyPersonId_field_name';
  const HYPERSONID_FIELD_CONFIG_VALUE = 'field_hypersonid';
  const STUDENT_ID = '123';
  const STUDENT_ID_FIELD_CONFIG_KEY = 'studentID_field_name';
  const STUDENT_ID_FIELD_CONFIG_VALUE = 'field_student_id';

  /** @var \Drupal\Core\Config\ConfigFactoryInterface*/
  private $configFactory;

  /** @var \Drupal\Core\Config\ImmutableConfig*/
  private $config;

  /** @var \Symfony\Component\DependencyInjection\ContainerInterface*/
  private $container;

  /** @var \Drupal\Core\Entity\EntityTypeManagerInterface*/
  private $entityTypeManager;

  /** @var \Drupal\samlauth\Event\SamlAuthUserSyncEvent*/
  private $event;

  /** @var \Drupal\Core\Field\FieldDefinitionInterface*/
  private $fieldDefinition;

  /** @var \Drupal\Core\Field\FieldItemListInterface*/
  private $fieldItemList;

  /** @var \Drupal\flag\FlagServiceInterface*/
  private $flagService;

  /** @var \Drupal\Core\Logger\LoggerChannel*/
  private $logger;

  /** @var \Drupal\Core\Messenger\MessengerInterface*/
  private $messenger;

  /** @var \Drupal\uhsg_oprek\Oprek\OprekServiceInterface*/
  private $oprekService;

  /** @var Drupal\uhsg_sisu\Services\StudyRightsServiceInterface*/
  private $studyRightsService;

  /** @var \Drupal\user\UserInterface*/
  private $user;

  /** @var \Drupal\uhsg_user_sync\SamlAuth\UserSyncSubscriber*/
  private $userSyncSubscriber;

  public function setUp() {
    parent::setUp();

    $this->config = $this->prophesize(ImmutableConfig::class);
    $this->config->get(self::COMMON_NAME_FIELD_CONFIG_KEY)->willReturn(self::COMMON_NAME_FIELD_CONFIG_VALUE);
    $this->config->get(self::HYPERSONID_FIELD_CONFIG_KEY)->willReturn(self::HYPERSONID_FIELD_CONFIG_VALUE);
    $this->config->get(self::STUDENT_ID_FIELD_CONFIG_KEY)->willReturn(self::STUDENT_ID_FIELD_CONFIG_VALUE);
    $this->config->get(self::TECHNICAL_CONDITION_FIELD_NAME)->willReturn(self::TECHNICAL_CONDITION_FIELD_NAME);
    $this->config->get(self::CODE_FIELD_NAME)->willReturn(self::CODE_FIELD_NAME);

    $this->configFactory = $this->prophesize(ConfigFactoryInterface::class);
    $this->configFactory->get('uhsg_user_sync.settings')->willReturn($this->config);

    $this->entityStorage = $this->prophesize(EntityStorageInterface::class);
    $this->entityTypeManager = $this->prophesize(EntityTypeManagerInterface::class);
    $this->entityTypeManager->getStorage('taxonomy_term')->willReturn($this->entityStorage);

    $this->fieldDefinition = $this->prophesize(FieldDefinitionInterface::class);

    $this->fieldItemList = $this->prophesize(FieldItemListInterface::class);
    $this->fieldItemList->getString()->willReturn(self::STUDENT_ID);

    $this->account = $this->prophesize(AccountInterface::class);

    $this->user = $this->prophesize(UserInterface::class);
    $this->user->getFieldDefinition(self::COMMON_NAME_FIELD_CONFIG_VALUE)->willReturn($this->fieldDefinition);
    $this->user->getFieldDefinition(self::HYPERSONID_FIELD_CONFIG_KEY)->willReturn($this->fieldDefinition);
    $this->user->getFieldDefinition(self::STUDENT_ID_FIELD_CONFIG_VALUE)->willReturn($this->fieldDefinition);
    $this->user->get(self::COMMON_NAME_FIELD_CONFIG_VALUE)->willReturn($this->fieldItemList);
    $this->user->get(self::HYPERSONID_FIELD_CONFIG_VALUE)->willReturn($this->fieldItemList);
    $this->user->get(self::STUDENT_ID_FIELD_CONFIG_VALUE)->willReturn($this->fieldItemList);
    $this->user->isAuthenticated()->willReturn(true);

    $this->event = $this->prophesize(SamlAuthUserSyncEvent::class);
    $this->event->getAccount()->willReturn($this->user);
    $this->event->getAttributes()->willReturn(self::ATTRIBUTES);

    $this->flagService = $this->prophesize(FlagServiceInterface::class);
    $this->logger = $this->prophesize(LoggerChannel::class);
    $this->messenger = $this->prophesize(MessengerInterface::class);
    $this->oprekService = $this->prophesize(OprekServiceInterface::class);
    $this->studyRightsService = $this->prophesize(StudyRightsServiceInterface::class);

    $this->container = $this->prophesize(ContainerInterface::class);

    Drupal::setContainer($this->container->reveal());

    $this->userSyncSubscriber = new UserSyncSubscriber(
      $this->configFactory->reveal(),
      $this->oprekService->reveal(),
      $this->studyRightsService->reveal(),
      $this->flagService->reveal(),
      $this->entityTypeManager->reveal(),
      $this->logger->reveal(),
      $this->messenger->reveal()
    );
  }

  /**
   * @test
   */
  public function shouldSubscribeToUserSyncEvent() {
    $events = $this->userSyncSubscriber->getSubscribedEvents();

    $this->assertEquals(['onUserSync'], $events[SamlAuthEvents::USER_SYNC][0]);
  }

  /**
   * @test
   */
  public function onUserSyncShouldDoNothingIfStudentIdFieldNameConfigIsMissing() {
    $this->config->get(self::COMMON_NAME_FIELD_CONFIG_KEY)->willReturn(NULL);
    $this->config->get(self::HYPERSONID_FIELD_CONFIG_KEY)->willReturn(NULL);
    $this->config->get(self::STUDENT_ID_FIELD_CONFIG_KEY)->willReturn(NULL);

    $this->event->getAccount()->shouldNotBeCalled();

    $this->userSyncSubscriber->onUserSync($this->event->reveal());
  }

  /**
   * @test
   */
  public function onUserSyncShouldSyncHyPersonIdWhenItHasChanged() {
    $this->fieldItemList->setValue(Argument::any())->shouldBeCalled();
    $this->event->markAccountChanged()->shouldBeCalled();

    $this->userSyncSubscriber->onUserSync($this->event->reveal());
  }

  /**
   * @test
   */
  public function onUserSyncShouldSyncStudentIdWhenItHasChanged() {
    $this->fieldItemList->setValue(Argument::any())->shouldBeCalled();
    $this->event->markAccountChanged()->shouldBeCalled();

    $this->userSyncSubscriber->onUserSync($this->event->reveal());
  }

}
