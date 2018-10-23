<?php

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\Tests\UnitTestCase;
use Drupal\uhsg_oprek\Oprek\OprekService;
use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

/**
 * @group uhsg
 */
class OprekServiceTest extends UnitTestCase {

  const BASE_URL = 'baseurl';
  const CERT_FILEPATH = 'certfilepath';
  const CERT_KEY_FILEPATH = 'certkeyfilepath';
  const STUDENT_NUMBER = '123';
  const STUDY_RIGHTS_RESPONSE = '{"status": 200, "data": []}';
  const VERSION = 123;
  const VERSION_RESPONSE = '{"status": 200, "data": {"version": ' . self::VERSION . '}}';

  /** @var \GuzzleHttp\Client*/
  private $client;

  /** @var \Drupal\Core\Config\ImmutableConfig*/
  private $config;

  /** @var \Drupal\Core\Config\ConfigFactoryInterface*/
  private $configFactory;

  /** @var \Drupal\uhsg_oprek\Oprek\OprekService*/
  private $oprekService;

  /** @var \Psr\Http\Message\ResponseInterface*/
  private $response;

  /** @var \Psr\Http\Message\StreamInterface*/
  private $stream;

  public function setUp() {
    parent::setUp();

    $this->stream = $this->prophesize(StreamInterface::class);
    $this->stream->getContents()->willReturn(self::STUDY_RIGHTS_RESPONSE);

    $this->response = $this->prophesize(ResponseInterface::class);
    $this->response->getStatusCode()->willReturn(200);
    $this->response->getBody()->willReturn($this->stream);

    $this->client = $this->prophesize(Client::class);

    $this->client->get(
      self::BASE_URL . '/students/' . self::STUDENT_NUMBER . '/studyrights',
      ['cert' => self::CERT_FILEPATH, 'ssl_key' => self::CERT_KEY_FILEPATH]
    )->willReturn($this->response);

    $this->client->get(
      self::BASE_URL . '/version',
      ['cert' => self::CERT_FILEPATH, 'ssl_key' => self::CERT_KEY_FILEPATH]
    )->willReturn($this->response);

    $this->config = $this->prophesize(ImmutableConfig::class);
    $this->config->get('base_url')->willReturn(self::BASE_URL);
    $this->config->get('cert_filepath')->willReturn(self::CERT_FILEPATH);
    $this->config->get('cert_key_filepath')->willReturn(self::CERT_KEY_FILEPATH);

    $this->configFactory = $this->prophesize(ConfigFactoryInterface::class);
    $this->configFactory->get('uhsg_oprek.settings')->willReturn($this->config);

    $this->oprekService = new OprekService($this->configFactory->reveal(), $this->client->reveal());
  }

  /**
   * @test
   */
  public function getStudyRightsShouldThrowExceptionWhenTheStudentNumberIsNotString() {
    $this->setExpectedException(\InvalidArgumentException::class);

    $this->oprekService->getStudyRights(NULL);
  }

  /**
   * @test
   */
  public function getStudyRightsShouldCallAPIUsingClientCertificate() {
    $this->client->get(
      self::BASE_URL . '/students/' . self::STUDENT_NUMBER . '/studyrights',
      ['cert' => self::CERT_FILEPATH, 'ssl_key' => self::CERT_KEY_FILEPATH]
    )->shouldBeCalled();

    $this->oprekService->getStudyRights(self::STUDENT_NUMBER);
  }

  /**
   * @test
   */
  public function getStudyRightsShouldThrowExceptionWhenAPIResponseCodeIsNot200() {
    $this->response->getStatusCode()->willReturn(500);

    $this->setExpectedException(\Exception::class);

    $this->oprekService->getStudyRights(self::STUDENT_NUMBER);
  }

  /**
   * @test
   */
  public function getVersionShouldReturnVersion() {
    $this->stream->getContents()->willReturn(self::VERSION_RESPONSE);

    $this->assertEquals(self::VERSION, $this->oprekService->getVersion());
  }

}
