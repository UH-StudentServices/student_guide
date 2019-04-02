<?php

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Logger\LoggerChannel;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Tests\UnitTestCase;
use Drupal\uhsg_active_degree_programme\ActiveDegreeProgrammeService;
use Drupal\uhsg_office_hours\OfficeHoursService;
use GuzzleHttp\Client;
use Prophecy\Argument;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @group uhsg
 */
class OfficeHoursServiceTest extends UnitTestCase {

  const CACHED_RESPONSE = ['degree_programme' => []];
  const CONFIG_API_BASE_URL = 'http://www.example.com/';
  const CONFIG_API_PATH = 'example';
  const CONFIG_CONNECT_TIMEOUT = 3;
  const CONFIG_REQUEST_TIMEOUT = 3;
  const EMPTY_RESPONSE = ['degree_programme' => []];
  const EXCEPTION_MESSAGE = 'Exception';
  const LANGUAGE = 'fi';

  /** @var \Drupal\uhsg_active_degree_programme\ActiveDegreeProgrammeService*/
  private $activeDegreeProgrammeService;

  /** @var \Drupal\Core\Cache\CacheBackendInterface*/
  private $cache;

  /** @var \GuzzleHttp\Client*/
  private $client;

  /** @var \Drupal\Core\Config\ImmutableConfig*/
  protected $config;

  /** @var \Drupal\Core\Config\ConfigFactory*/
  protected $configFactory;

  /** @var \Symfony\Component\DependencyInjection\ContainerInterface*/
  private $container;

  /** @var \Drupal\Core\Entity\EntityStorageInterface*/
  private $entityStorage;

  /** @var \Drupal\Core\Entity\EntityTypeManagerInterface*/
  private $entityTypeManager;

  /** @var \Drupal\Core\Language\LanguageInterface*/
  protected $language;

  /** @var \Drupal\Core\Language\LanguageManagerInterface*/
  protected $languageManager;

  /** @var \Drupal\Core\Logger\LoggerChannel*/
  private $logger;

  /** @var MessengerInterface*/
  private $messenger;

  /** @var \Drupal\uhsg_office_hours\OfficeHoursService*/
  private $officeHoursService;

  /** @var \Psr\Http\Message\ResponseInterface*/
  private $response;

  /** @var string */
  private $responseJson;

  /** @var \Drupal\Component\Datetime\TimeInterface*/
  private $time;

  public function setUp() {
    parent::setUp();

    $this->activeDegreeProgrammeService = $this->prophesize(ActiveDegreeProgrammeService::class);

    $this->cache = $this->prophesize(CacheBackendInterface::class);
    $this->cache->get(Argument::any())->willReturn(FALSE);

    $this->config = $this->prophesize(ImmutableConfig::class);
    $this->config->get(OfficeHoursService::CONFIG_API_BASE_URL)->willReturn(self::CONFIG_API_BASE_URL);
    $this->config->get(OfficeHoursService::CONFIG_API_PATH)->willReturn(self::CONFIG_API_PATH);
    $this->config->get(OfficeHoursService::CONFIG_CONNECT_TIMEOUT)->willReturn(self::CONFIG_CONNECT_TIMEOUT);
    $this->config->get(OfficeHoursService::CONFIG_REQUEST_TIMEOUT)->willReturn(self::CONFIG_REQUEST_TIMEOUT);

    $this->configFactory = $this->prophesize(ConfigFactory::class);
    $this->configFactory->get(OfficeHoursService::CONFIG_NAME)->willReturn($this->config);

    $this->response = $this->prophesize(ResponseInterface::class);
    $this->response->getStatusCode()->willReturn(200);

    $this->responseJson = file_get_contents(__DIR__ . '/office-hours.json');

    $this->client = $this->prophesize(Client::class);
    $this->client->get(Argument::any(), Argument::any())->willReturn($this->response);

    $this->entityStorage = $this->prophesize(EntityStorageInterface::class);

    $this->entityTypeManager = $this->prophesize(EntityTypeManagerInterface::class);
    $this->entityTypeManager->getStorage('taxonomy_term')->willReturn($this->entityStorage);

    $this->language = $this->prophesize(LanguageInterface::class);
    $this->language->getId()->willReturn(self::LANGUAGE);

    $this->languageManager = $this->prophesize(LanguageManagerInterface::class);
    $this->languageManager->getCurrentLanguage()->willReturn($this->language);

    $this->logger = $this->prophesize(LoggerChannel::class);
    $this->messenger = $this->prophesize(MessengerInterface::class);
    $this->time = $this->prophesize(TimeInterface::class);

    $this->container = $this->prophesize(ContainerInterface::class);
    Drupal::setContainer($this->container->reveal());

    $this->officeHoursService = new OfficeHoursServiceTestDouble(
      $this->cache->reveal(),
      $this->client->reveal(),
      $this->configFactory->reveal(),
      $this->logger->reveal(),
      $this->time->reveal(),
      $this->activeDegreeProgrammeService->reveal(),
      $this->languageManager->reveal(),
      $this->messenger->reveal()
    );
  }

  /**
   * @test
   */
  public function shouldReturnTheOfficeHoursFromCacheWhenCachedResponseExists() {
    $cacheEntry = new stdClass();
    $cacheEntry->data = self::CACHED_RESPONSE;
    $this->cache->get(Argument::any())->willReturn($cacheEntry);

    $this->client->get(Argument::any(), Argument::any())->shouldNotBeCalled();

    $this->assertEquals(self::CACHED_RESPONSE, $this->officeHoursService->getOfficeHours());
  }

  /**
   * @test
   */
  public function shouldLogApiRequestException() {
    $this->client->get(Argument::any(), Argument::any())->willThrow(new Exception(self::EXCEPTION_MESSAGE));

    $this->logger->error(self::EXCEPTION_MESSAGE)->shouldBeCalled();

    $this->officeHoursService->getOfficeHours();
  }

  /**
   * @test
   */
  public function shouldAddDisplayableErrorMessageOnApiRequestException() {
    $this->client->get(Argument::any(), Argument::any())->willThrow(new Exception(self::EXCEPTION_MESSAGE));

    $this->messenger->addError(Argument::any())->shouldBeCalled();

    $this->officeHoursService->getOfficeHours();
  }

  /**
   * @test
   */
  public function shouldReturnAnEmptyArrayWhenResponseCodeIsNot200() {
    $this->response->getStatusCode()->willReturn(404);

    $this->assertEquals(self::EMPTY_RESPONSE, $this->officeHoursService->getOfficeHours());
  }

  /**
   * @test
   */
  public function shouldReturnAnEmptyArrayWhenTheResponseIsEmpty() {
    $this->response->getBody()->willReturn('[]');

    $this->assertEquals(self::EMPTY_RESPONSE, $this->officeHoursService->getOfficeHours());
  }

  /**
   * @test
   */
  public function shouldGroupOfficeHoursByDegreeProgrammeAndGeneral() {
    $this->response->getBody()->willReturn($this->responseJson);

    $officeHours = $this->officeHoursService->getOfficeHours();

    $this->assertArrayHasKey('degree_programme', $officeHours);
    $this->assertArrayHasKey('general', $officeHours);
    $this->assertEquals(2, count(array_keys($officeHours)));
  }

  /**
   * @test
   */
  public function shouldReturnOnlyGeneralOfficeHoursWhenThereIsNoActiveDegreeProgramme() {
    $this->response->getBody()->willReturn($this->responseJson);
    $this->activeDegreeProgrammeService->getTerm()->willReturn(NULL);

    $officeHours = $this->officeHoursService->getOfficeHours();

    $this->assertEmpty($officeHours['degree_programme']);
    $this->assertNotEmpty($officeHours['general']);
  }

}

/**
 * Test double for overriding difficult to test methods.
 */
class OfficeHoursServiceTestDouble extends OfficeHoursService {

  public function t($string, array $args = [], array $options = []) {
    return $string;
  }

}
