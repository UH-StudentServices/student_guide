<?php

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannel;
use Drupal\flag\FlagServiceInterface;
use Drupal\Tests\UnitTestCase;
use Drupal\samlauth\Event\SamlAuthEvents;
use Drupal\samlauth\Event\SamlAuthUserSyncEvent;
use Drupal\uhsg_oprek\Oprek\OprekServiceInterface;
use Drupal\uhsg_user_sync\SamlAuth\UserSyncSubscriber;

/**
 * @group uhsg
 */
class UserSynscSubscriberTest extends UnitTestCase {

  const ATTRIBUTES = [];
  const STUDENT_ID_FIELD_CONFIG_KEY = 'studentID_field_name';
  const STUDENT_ID_FIELD_CONFIG_VALUE = 'field_student_id';

  /** @var ConfigFactoryInterface */
  private $configFactory;

  /** @var ImmutableConfig */
  private $config;

  /** @var EntityTypeManagerInterface */
  private $entityTypeManager;

  /** @var SamlAuthUserSyncEvent */
  private $event;

  /** @var FlagServiceInterface */
  private $flagService;

  /** @var LoggerChannel */
  private $logger;

  /** @var OprekServiceInterface */
  private $oprekService;

  /** @var UserSyncSubscriber */
  private $userSyncSubscriber;

  public function setUp() {
    parent::setUp();

    $this->config = $this->prophesize(ImmutableConfig::class);
    $this->config->get(self::STUDENT_ID_FIELD_CONFIG_KEY)->willReturn(self::STUDENT_ID_FIELD_CONFIG_VALUE);

    $this->configFactory = $this->prophesize(ConfigFactoryInterface::class);
    $this->configFactory->get('uhsg_user_sync.settings')->willReturn($this->config);

    $this->entityTypeManager = $this->prophesize(EntityTypeManagerInterface::class);

    $this->event = $this->prophesize(SamlAuthUserSyncEvent::class);
    $this->event->getAttributes()->willReturn(self::ATTRIBUTES);

    $this->flagService = $this->prophesize(FlagServiceInterface::class);
    $this->logger = $this->prophesize(LoggerChannel::class);
    $this->oprekService = $this->prophesize(OprekServiceInterface::class);

    $this->userSyncSubscriber = new UserSyncSubscriber(
      $this->configFactory->reveal(),
      $this->oprekService->reveal(),
      $this->flagService->reveal(),
      $this->entityTypeManager->reveal(),
      $this->logger->reveal()
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
    $this->config->get(self::STUDENT_ID_FIELD_CONFIG_KEY)->willReturn(NULL);

    $this->event->getAccount()->shouldNotBeCalled();

    $this->userSyncSubscriber->onUserSync($this->event->reveal());
  }
}