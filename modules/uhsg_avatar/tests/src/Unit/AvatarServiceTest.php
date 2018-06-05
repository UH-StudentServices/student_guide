<?php

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\Logger\LoggerChannel;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Tests\UnitTestCase;
use Drupal\uhsg_avatar\AvatarService;
use GuzzleHttp\Client;

/**
 * @group uhsg
 */
class AvatarServiceTest extends UnitTestCase {

  const ADMIN_UID = 1;
  
  /** @var AvatarService */
  private $avatarService;
  
  /** @var CacheBackendInterface */
  private $cache;

  /** @var Client */
  private $client;

  /** @var ImmutableConfig */
  private $config;

  /** @var ConfigFactory */
  private $configFactory;

  /** @var AccountProxyInterface */
  private $currentUser;

  /** @var LoggerChannel */
  private $logger;

  public function setUp() {
    parent::setUp();

    $this->config = $this->prophesize(ImmutableConfig::class);
    $this->configFactory = $this->prophesize(ConfigFactory::class);
    $this->currentUser = $this->prophesize(AccountProxyInterface::class);
    $this->client = $this->prophesize(Client::class);
    $this->logger = $this->prophesize(LoggerChannel::class);
    $this->cache = $this->prophesize(CacheBackendInterface::class);
    
    $this->avatarService = new AvatarService(
      $this->configFactory->reveal(),
      $this->currentUser->reveal(),
      $this->client->reveal(),
      $this->logger->reveal(),
      $this->cache->reveal()
    );
  }

  /**
   * @test
   */
  public function shouldNotFetchAvatarForAnonymousUser() {
    $this->currentUser->isAuthenticated()->willReturn(FALSE);

    $this->assertNull($this->avatarService->getAvatar());
  }

  /**
   * @test
   */
  public function shouldNotFetchAvatarForAdmin() {
    $this->currentUser->isAuthenticated()->willReturn(TRUE);
    $this->currentUser->id()->willReturn(self::ADMIN_UID);

    $this->assertNull($this->avatarService->getAvatar());
  }
}
