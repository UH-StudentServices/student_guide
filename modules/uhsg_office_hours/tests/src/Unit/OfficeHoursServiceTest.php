<?php

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannel;
use Drupal\taxonomy\TermInterface;
use Drupal\Tests\UnitTestCase;
use Drupal\uhsg_office_hours\OfficeHoursService;
use GuzzleHttp\Client;
use Prophecy\Argument;
use Psr\Http\Message\ResponseInterface;

/**
 * @group uhsg
 */
class OfficeHoursServiceTest extends UnitTestCase {

  const CACHED_RESPONSE = ['cached response'];
  const EXCEPTION_MESSAGE = 'Exception';
  
  /** @var CacheBackendInterface */
  private $cache;

  /** @var Client */
  private $client;

  /** @var EntityTypeManagerInterface */
  private $entityTypeManager;

  /** @var LoggerChannel */
  private $logger;

  /** @var OfficeHoursService */
  private $officeHoursService;

  /** @var ResponseInterface */
  private $response;

  /** @var TimeInterface */
  private $time;
  
  public function setUp() {
    parent::setUp();

    $this->cache = $this->prophesize(CacheBackendInterface::class);
    $this->cache->get(Argument::any())->willReturn(FALSE);

    $this->response = $this->prophesize(ResponseInterface::class);
    $this->response->getStatusCode()->willReturn(200);

    $this->client = $this->prophesize(Client::class);
    $this->client->get(Argument::any())->willReturn($this->response);

    $this->entityTypeManager = $this->prophesize(EntityTypeManagerInterface::class);
    $this->logger = $this->prophesize(LoggerChannel::class);
    $this->time = $this->prophesize(TimeInterface::class);
    
    $this->officeHoursService = new OfficeHoursService(
      $this->client->reveal(),
      $this->cache->reveal(),
      $this->entityTypeManager->reveal(),
      $this->time->reveal(),
      $this->logger->reveal()
    );
  }

  /**
   * @test
   */
  public function shouldReturnTheOfficeHoursFromCacheWhenCachedResponseExists() {
    $cacheEntry = new stdClass();
    $cacheEntry->data = self::CACHED_RESPONSE;
    $this->cache->get(OfficeHoursService::CACHE_KEY)->willReturn($cacheEntry);

    $this->client->get(Argument::any())->shouldNotBeCalled();

    $this->assertEquals(self::CACHED_RESPONSE, $this->officeHoursService->getOfficeHours());
  }

  /**
   * @test
   */
  public function shouldLogApiRequestException() {
    $this->client->get(Argument::any())->willThrow(new Exception(self::EXCEPTION_MESSAGE));

    $this->logger->error(self::EXCEPTION_MESSAGE)->shouldBeCalled();

    $this->officeHoursService->getOfficeHours();
  }

  /**
   * @test
   */
  public function shouldReturnAnEmptyArrayWhenResponseCodeIsNot200() {
    $this->response->getStatusCode()->willReturn(404);

    $this->assertEmpty($this->officeHoursService->getOfficeHours());
  }
}