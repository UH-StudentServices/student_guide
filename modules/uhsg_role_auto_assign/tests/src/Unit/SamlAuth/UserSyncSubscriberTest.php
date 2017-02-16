<?php

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Logger\LoggerChannelFactory;
use Drupal\Tests\UnitTestCase;
use Drupal\samlauth\Event\SamlAuthEvents;
use Drupal\uhsg_role_auto_assign\SamlAuth\UserSyncSubscriber;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @group uhsg
 */
class UserSyncSubscriberTest extends UnitTestCase {

  /** @var ConfigFactoryInterface */
  private $configFactory;

  /** @var ContainerInterface */
  private $container;

  /** @var LoggerChannelFactory */
  private $loggerChannelFactory;

  /** @var UserSyncSubscriber */
  private $userSyncSubscriber;

  public function setUp() {
    parent::setUp();

    $this->configFactory = $this->prophesize(ConfigFactoryInterface::class);
    $this->loggerChannelFactory = $this->prophesize(LoggerChannelFactory::class);

    $this->container = $this->prophesize(ContainerInterface::class);
    $this->container->get('config.factory')->willReturn($this->configFactory);
    $this->container->get('logger.factory')->willReturn($this->loggerChannelFactory);

    \Drupal::setContainer($this->container->reveal());

    $this->userSyncSubscriber = new UserSyncSubscriber();
  }

  /**
   * @test
   */
  public function shouldSubcribeToUserSyncEvent() {
    $events = $this->userSyncSubscriber->getSubscribedEvents();

    $this->assertEquals('onUserSync', $events[SamlAuthEvents::USER_SYNC][0][0]);
  }
}
