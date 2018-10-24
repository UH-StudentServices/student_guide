<?php

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\EntityTypeRepositoryInterface;
use Drupal\Core\Logger\LoggerChannelFactory;
use Drupal\samlauth\Event\SamlAuthEvents;
use Drupal\samlauth\Event\SamlAuthUserSyncEvent;
use Drupal\Tests\UnitTestCase;
use Drupal\uhsg_role_auto_assign\SamlAuth\UserSyncSubscriber;
use Drupal\user\Entity\Role;
use Drupal\user\UserInterface;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @group uhsg
 */
class UserSyncSubscriberTest extends UnitTestCase {

  const GROUP_1_NAME = 'Group 1 name';
  const GROUP_2_NAME = 'Group 2 name';
  const GROUP_TO_ROLE_MAPPING = [['rid' => self::ROLE_1_ID, 'group_name' => self::GROUP_1_NAME]];
  const ROLE_1_ID = 1;
  const ROLE_2_ID = 2;
  const USER_NAME = 'User name';

  /** @var \Drupal\user\UserInterface*/
  private $account;

  /** @var \Drupal\Core\Config\ConfigFactoryInterface*/
  private $configFactory;

  /** @var \Drupal\Core\Config\ImmutableConfig*/
  private $config;

  /** @var \Symfony\Component\DependencyInjection\ContainerInterface*/
  private $container;

  /** @var \Drupal\Core\Entity\EntityStorageInterface*/
  private $entityStorage;

  /** @var \Drupal\Core\Entity\EntityTypeManagerInterface*/
  private $entityTypeManager;

  /** @var \Drupal\Core\Entity\EntityTypeRepositoryInterface*/
  private $entityTypeRepository;

  /** @var \Drupal\samlauth\Event\SamlAuthUserSyncEvent*/
  private $event;

  /** @var \Psr\Log\LoggerInterface*/
  private $logger;

  /** @var \Drupal\Core\Logger\LoggerChannelFactory*/
  private $loggerChannelFactory;

  /** @var \Drupal\user\Entity\Role*/
  private $role;

  /** @var \Drupal\uhsg_role_auto_assign\SamlAuth\UserSyncSubscriber*/
  private $userSyncSubscriber;

  public function setUp() {
    parent::setUp();

    $this->account = $this->prophesize(UserInterface::class);
    $this->account->hasRole(Argument::any())->willReturn(FALSE);
    $this->account->label()->willReturn(self::USER_NAME);

    $this->config = $this->prophesize(ImmutableConfig::class);
    $this->config->get('group_to_roles')->willReturn(self::GROUP_TO_ROLE_MAPPING);

    $this->configFactory = $this->prophesize(ConfigFactoryInterface::class);
    $this->configFactory->get('uhsg_role_auto_assign.settings')->willReturn($this->config);

    $this->role = $this->prophesize(Role::class);
    $this->role->id()->willReturn(self::ROLE_1_ID);

    $this->entityStorage = $this->prophesize(EntityStorageInterface::class);
    $this->entityStorage->load(Argument::any())->willReturn($this->role);

    $this->event = $this->prophesize(SamlAuthUserSyncEvent::class);
    $this->event->getAccount()->willReturn($this->account);
    $this->event->getAttributes()->willReturn(['urn:mace:funet.fi:helsinki.fi:hyGroupCn' => [self::GROUP_1_NAME]]);

    $this->logger = $this->prophesize(LoggerInterface::class);

    $this->loggerChannelFactory = $this->prophesize(LoggerChannelFactory::class);
    $this->loggerChannelFactory->get(Argument::any())->willReturn($this->logger);

    $this->entityTypeManager = $this->prophesize(EntityTypeManagerInterface::class);
    $this->entityTypeManager->getStorage(Argument::any())->willReturn($this->entityStorage);

    $this->entityTypeRepository = $this->prophesize(EntityTypeRepositoryInterface::class);
    $this->entityTypeRepository->getEntityTypeFromClass(Argument::any())->willReturnArgument(0);

    $this->container = $this->prophesize(ContainerInterface::class);
    $this->container->get('config.factory')->willReturn($this->configFactory);
    $this->container->get('entity_type.manager')->willReturn($this->entityTypeManager);
    $this->container->get('entity_type.repository')->willReturn($this->entityTypeRepository);
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

  /**
   * @test
   */
  public function shouldAssignRoleToUserWhenTheUserBelongsToAssignableGroup() {
    $this->account->addRole(Argument::any())->shouldBeCalled();
    $this->event->markAccountChanged()->shouldBeCalled();

    $this->userSyncSubscriber->onUserSync($this->event->reveal());
  }

  /**
   * @test
   */
  public function shouldUnassignRoleFromUserWhenTheUserDoesNotBelongToAssignableGroup() {
    $this->account->hasRole(Argument::any())->willReturn(TRUE);
    $this->event->getAttributes()->willReturn(['urn:mace:funet.fi:helsinki.fi:hyGroupCn' => [self::GROUP_2_NAME]]);

    $this->account->removeRole(Argument::any())->shouldBeCalled();
    $this->event->markAccountChanged()->shouldBeCalled();

    $this->userSyncSubscriber->onUserSync($this->event->reveal());
  }

  /**
   * @test
   */
  public function shouldNotModifyRolesWhenTheUserDoesNotBelongToAnyGroups() {
    $this->event->getAttributes()->willReturn(['urn:mace:funet.fi:helsinki.fi:hyGroupCn' => []]);

    $this->account->addRole(Argument::any())->shouldNotBeCalled();
    $this->account->removeRole(Argument::any())->shouldNotBeCalled();

    $this->userSyncSubscriber->onUserSync($this->event->reveal());
  }

  /**
   * @test
   */
  public function shouldNotModifyRolesWhenThereAreNoGroupToRoleMappings() {
    $this->config->get('group_to_roles')->willReturn([]);

    $this->account->addRole(Argument::any())->shouldNotBeCalled();
    $this->account->removeRole(Argument::any())->shouldNotBeCalled();

    $this->userSyncSubscriber->onUserSync($this->event->reveal());
  }

  /**
   * @test
   */
  public function shouldNotModifyRolesWhenTheUserAlreadyHasGroupRoleAssigned() {
    $this->account->hasRole(Argument::any())->willReturn(TRUE);

    $this->account->addRole(Argument::any())->shouldNotBeCalled();
    $this->account->removeRole(Argument::any())->shouldNotBeCalled();

    $this->userSyncSubscriber->onUserSync($this->event->reveal());
  }

}
