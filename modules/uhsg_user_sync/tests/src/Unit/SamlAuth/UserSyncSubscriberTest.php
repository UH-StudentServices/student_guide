<?php

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannel;
use Drupal\flag\FlagServiceInterface;
use Drupal\Tests\UnitTestCase;
use Drupal\samlauth\Event\SamlAuthEvents;
use Drupal\uhsg_oprek\Oprek\OprekServiceInterface;
use Drupal\uhsg_user_sync\SamlAuth\UserSyncSubscriber;

/**
 * @group uhsg
 */
class UserSynscSubscriberTest extends UnitTestCase {

  /** @var ConfigFactoryInterface */
  private $configFactory;

  /** @var EntityTypeManagerInterface */
  private $entityTypeManager;

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

    $this->configFactory = $this->prophesize(ConfigFactoryInterface::class);
    $this->entityTypeManager = $this->prophesize(EntityTypeManagerInterface::class);
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
}