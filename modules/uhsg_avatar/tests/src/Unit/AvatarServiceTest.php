<?php

use Drupal\Component\Datetime\TimeInterface;
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

  /** @var \Drupal\uhsg_avatar\AvatarService*/
  private $avatarService;

  /** @var \Drupal\Core\Cache\CacheBackendInterface*/
  private $cache;

  /** @var object*/
  private $cachedUrl;

  /** @var \GuzzleHttp\Client*/
  private $client;

  /** @var \Drupal\Core\Config\ImmutableConfig*/
  private $config;

  /** @var \Drupal\Core\Config\ConfigFactory*/
  private $configFactory;

  /** @var \Symfony\Component\DependencyInjection\ContainerInterface*/
  private $container;

  /** @var \Drupal\Core\Session\AccountProxyInterface*/
  private $currentUser;

  /** @var \Drupal\Core\Logger\LoggerChannel*/
  private $logger;

  /** @var \Psr\Http\Message\ResponseInterface*/
  private $response;

  /** @var \Drupal\Component\Datetime\TimeInterface*/
  private $time;

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

    $this->time = $this->prophesize(TimeInterface::class);

    $this->container = $this->prophesize(ContainerInterface::class);

    Drupal::setContainer($this->container->reveal());

    $this->avatarService = new AvatarServiceTestDouble(
      $this->configFactory->reveal(),
      $this->currentUser->reveal(),
      $this->client->reveal(),
      $this->logger->reveal(),
      $this->cache->reveal(),
      $this->time->reveal()
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
  public function shouldReturnAvatarImageUrlFromApiWhenUrlNotInCache() {
    $this->currentUser->isAuthenticated()->willReturn(TRUE);
    $this->currentUser->id()->willReturn(self::NORMAL_USER_UID);

    $this->assertEquals(self::AVATAR_IMAGE_URL, $this->avatarService->getAvatar());
  }

  /**
   * @test
   */
  public function shouldReturnAvatarImageUrlFromCacheWhenUrlInCache() {
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
  public function shouldLogApiException() {
    $this->currentUser->isAuthenticated()->willReturn(TRUE);
    $this->currentUser->id()->willReturn(self::NORMAL_USER_UID);
    $this->client->get(Argument::any())->willThrow(new Exception(self::EXCEPTION_MESSAGE));

    $this->logger->error(self::EXCEPTION_MESSAGE)->shouldBeCalled();

    $this->avatarService->getAvatar();
  }

  /**
   * @test
   */
  public function shouldReturnNullUrlWhenApiStatusOtherThan200() {
    $this->currentUser->isAuthenticated()->willReturn(TRUE);
    $this->currentUser->id()->willReturn(self::NORMAL_USER_UID);
    $this->response->getStatusCode()->willReturn(404);

    $this->assertNull($this->avatarService->getAvatar());
  }

}

/**
 * Test double for overriding difficult to test methods.
 */
class AvatarServiceTestDouble extends AvatarService {

  protected function loadUser($id) {
    $prophet = new Prophet();

    $fieldItemList = $prophet->prophesize(FieldItemListInterface::class);
    $fieldItemList->getString()->willReturn('hy-hlo-123');

    $user = $prophet->prophesize(User::class);
    $user->get('field_hypersonid')->willReturn($fieldItemList->reveal());

    return $user->reveal();
  }

}
