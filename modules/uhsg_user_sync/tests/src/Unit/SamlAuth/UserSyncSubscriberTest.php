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
use Drupal\uhsg_user_sync\SamlAuth\UserSyncSubscriber;
use Drupal\user\UserInterface;
use Prophecy\Argument;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Messenger\MessengerInterface;

/**
 * @group uhsg
 */
class UserSynscSubscriberTest extends UnitTestCase {

  const ATTRIBUTES = ['studentId'];
  const STUDENT_ID = '123';
  const OODI_UID_FIELD_CONFIG_KEY = 'oodiUID_field_name';
  const OODI_UID_FIELD_CONFIG_VALUE = 'field_oodi_uid';
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

  /** @var \Drupal\user\UserInterface*/
  private $user;

  /** @var \Drupal\uhsg_user_sync\SamlAuth\UserSyncSubscriber*/
  private $userSyncSubscriber;

  public function setUp() {
    parent::setUp();

    $this->config = $this->prophesize(ImmutableConfig::class);
    $this->config->get(self::OODI_UID_FIELD_CONFIG_KEY)->willReturn(self::OODI_UID_FIELD_CONFIG_VALUE);
    $this->config->get(self::STUDENT_ID_FIELD_CONFIG_KEY)->willReturn(self::STUDENT_ID_FIELD_CONFIG_VALUE);

    $this->configFactory = $this->prophesize(ConfigFactoryInterface::class);
    $this->configFactory->get('uhsg_user_sync.settings')->willReturn($this->config);

    $this->entityTypeManager = $this->prophesize(EntityTypeManagerInterface::class);

    $this->fieldDefinition = $this->prophesize(FieldDefinitionInterface::class);

    $this->fieldItemList = $this->prophesize(FieldItemListInterface::class);
    $this->fieldItemList->getString()->willReturn(self::STUDENT_ID);

    $this->user = $this->prophesize(UserInterface::class);
    $this->user->getFieldDefinition(self::OODI_UID_FIELD_CONFIG_VALUE)->willReturn($this->fieldDefinition);
    $this->user->getFieldDefinition(self::STUDENT_ID_FIELD_CONFIG_VALUE)->willReturn($this->fieldDefinition);
    $this->user->get(self::OODI_UID_FIELD_CONFIG_VALUE)->willReturn($this->fieldItemList);
    $this->user->get(self::STUDENT_ID_FIELD_CONFIG_VALUE)->willReturn($this->fieldItemList);

    $this->event = $this->prophesize(SamlAuthUserSyncEvent::class);
    $this->event->getAccount()->willReturn($this->user);
    $this->event->getAttributes()->willReturn(self::ATTRIBUTES);

    $this->flagService = $this->prophesize(FlagServiceInterface::class);
    $this->logger = $this->prophesize(LoggerChannel::class);
    $this->messenger = $this->prophesize(MessengerInterface::class);
    $this->oprekService = $this->prophesize(OprekServiceInterface::class);

    $this->container = $this->prophesize(ContainerInterface::class);

    Drupal::setContainer($this->container->reveal());

    $this->userSyncSubscriber = new UserSyncSubscriber(
      $this->configFactory->reveal(),
      $this->oprekService->reveal(),
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
    $this->config->get(self::OODI_UID_FIELD_CONFIG_KEY)->willReturn(NULL);
    $this->config->get(self::STUDENT_ID_FIELD_CONFIG_KEY)->willReturn(NULL);

    $this->event->getAccount()->shouldNotBeCalled();

    $this->userSyncSubscriber->onUserSync($this->event->reveal());
  }

  /**
   * @test
   */
  public function onUserSyncShouldSyncOodiUidWhenItHasChanged() {
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
