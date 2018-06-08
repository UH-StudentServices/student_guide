<?php

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Logger\LoggerChannel;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Tests\UnitTestCase;
use Drupal\uhsg_avatar\AvatarService;
use Drupal\user\Entity\User;
use GuzzleHttp\Client;
use Prophecy\Argument;
use Prophecy\Prophet;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @group uhsg
 */
class AvatarServiceTest extends UnitTestCase {

  const ADMIN_UID = 1;
  const AVATAR_IMAGE_URL = 'http://www.example.com';
  const EXCEPTION_MESSAGE = 'Exception message';
  const NORMAL_USER_UID = 2;
  const RESPONSE_BODY = '{"avatarImageUrl": "http:\/\/www.example.com"}';
  
  /** @var AvatarService */
  private $avatarService;
  
  /** @var CacheBackendInterface */
  private $cache;

  /** @var object */
  private $cachedUrl;

  /** @var Client */
  private $client;

  /** @var ImmutableConfig */
  private $config;

  /** @var ConfigFactory */
  private $configFactory;

  /** @var ContainerInterface */
  private $container;

  /** @var AccountProxyInterface */
  private $currentUser;

  /** @var LoggerChannel */
  private $logger;

  /** @var ResponseInterface */
  private $response;

  public function setUp() {
    parent::setUp();

    $this->config = $this->prophesize(ImmutableConfig::class);
    $this->config->get('api_base_url')->willReturn('');
    $this->config->get('api_path')->willReturn('');

    $this->configFactory = $this->prophesize(ConfigFactory::class);
    $this->configFactory->get('uhsg_avatar.config')->willReturn($this->config);

    $this->currentUser = $this->prophesize(AccountProxyInterface::class);

    $this->response = $this->prophesize(ResponseInterface::class);
    $this->response->getStatusCode()->willReturn(200);
    $this->response->getBody()->willReturn(self::RESPONSE_BODY);

    $this->client = $this->prophesize(Client::class);
    $this->client->get(Argument::any())->willReturn($this->response);

    $this->logger = $this->prophesize(LoggerChannel::class);
    $this->cache = $this->prophesize(CacheBackendInterface::class);

    $this->cachedUrl = new stdClass();
    $this->cachedUrl->data = self::AVATAR_IMAGE_URL;

    $this->container = $this->prophesize(ContainerInterface::class);

    Drupal::setContainer($this->container->reveal());
    
    $this->avatarService = new AvatarServiceTestDouble(
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

  /**
   * @test
   */
  public function shouldReturnAvatarImageURLFromAPIWhenURLNotInCache() {
    $this->currentUser->isAuthenticated()->willReturn(TRUE);
    $this->currentUser->id()->willReturn(self::NORMAL_USER_UID);

    $this->assertEquals(self::AVATAR_IMAGE_URL, $this->avatarService->getAvatar());
  }

  /**
   * @test
   */
  public function shouldReturnAvatarImageURLFromCacheWhenURLInCache() {
    $this->currentUser->isAuthenticated()->willReturn(TRUE);
    $this->currentUser->id()->willReturn(self::NORMAL_USER_UID);
    $this->cache->get(Argument::any())->willReturn($this->cachedUrl);

    $this->cache->get(Argument::any())->shouldBeCalled();
    $this->client->get(Argument::any())->shouldNotBeCalled();

    $this->assertEquals(self::AVATAR_IMAGE_URL, $this->avatarService->getAvatar());
  }

  /**
   * @test
   */
  public function shouldLogAPIException() {
    $this->currentUser->isAuthenticated()->willReturn(TRUE);
    $this->currentUser->id()->willReturn(self::NORMAL_USER_UID);
    $this->client->get(Argument::any())->willThrow(new Exception(self::EXCEPTION_MESSAGE));

    $this->logger->error(self::EXCEPTION_MESSAGE)->shouldBeCalled();

    $this->avatarService->getAvatar();
  }
}

/**
 * Test double for overriding difficult to test methods.
 */
class AvatarServiceTestDouble extends AvatarService {

  protected function loadUser($id) {
    $prophet = new Prophet();

    $fieldItemList = $prophet->prophesize(FieldItemListInterface::class);
    $fieldItemList->getString()->willReturn('123');

    $user = $prophet->prophesize(User::class);
    $user->get('field_oodi_uid')->willReturn($fieldItemList->reveal());

    return $user->reveal();
  }
}