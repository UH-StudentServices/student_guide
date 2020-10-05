<?php

namespace Drupal\uhsg_sisu\Services;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Logger\RfcLogLevel;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Site\Settings;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Client;

/**
 * Class SisuService.
 *
 * @package Drupal\uhsg_sisu\Services\SisuService
 */
class SisuService {

  /**
   * Default API url.
   *
   * @var string
   */
  const GRAPHQL_URL = 'https://esbmt2.it.helsinki.fi/secure/doo-sisu/graphql';

  /**
   * Default CERT path.
   *
   * @var string
   */
  const GRAPHQL_CERT_PATH = '/etc/ssl/certs/esb/esb.pem';

  /**
   * Default SSLKEY path.
   *
   * @var string
   */
  const GRAPHQL_SSLKEY_PATH = '/etc/ssl/certs/esb/esb.key';

  /**
   * The HTTP client.
   *
   * @var \GuzzleHttp\Client
   */
  private $client;

  /**
   * Logger Factory.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  private $loggerFactory;

  /**
   * Service constructor.
   *
   * @param \GuzzleHttp\Client $client
   *   Http Client.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $loggerFactory
   *   LoggerChannelFactory.
   */
  public function __construct(Client $client, 
                              LoggerChannelFactoryInterface $loggerFactory) {
    $this->client = $client;
    $this->loggerFactory = $loggerFactory;
  }

  /**
   * Get api url.
   *
   * @return string
   *   The absolute API url as a string.
   */
  public function getGraphQlUrl() {
    return Settings::get('uhsg_sisu_graphql_url', self::GRAPHQL_URL);
  }

  /**
   * Gets information regarding the given realisation identified by ID.
   *
   * @param array $graphQlQuery
   *   Realisation ID.
   *
   * @return \Psr\Http\Message\ResponseInterface
   *   Response object.
   */
  public function apiRequest(array $graphQlQuery) {
    $url = $this->getGraphQlUrl();
    $data = Json::encode($graphQlQuery);

    return $this->request($url, 'GET', $data);
  }

  /**
   * Executes a request and returns the response.
   *
   * @param string $url
   *   Request URL.
   * @param string $method
   *   Request method.
   * @param null|mixed $data
   *   Optional request data.
   * @param array $option_overrides
   *   Optional options overrides.
   *
   * @return \Psr\Http\Message\ResponseInterface
   *   Response object.
   */
  private function request($url, $method, $data = NULL, array $option_overrides = []) {
    $options = $option_overrides + $this->getRequestOptions($data);

    // Guzzle Client.
    $response = $this->client->request(
      $method,
      $url,
      $options
    );

    return $this->handleResponse($response);
  }

  /**
   * Constructs and returns request options.
   *
   * @param null|mixed $data
   *   Optional request data.
   *
   * @return array
   *   Request options.
   */
  private function getRequestOptions($data) {
    $options = [
      'timeout' => "10",
      'verify' => TRUE,
      'http_errors' => FALSE,
      'cert' => $this->settings->get('uhsg_sisu_cert_path', self::GRAPHQL_CERT_PATH),
      'ssl_key' => $this->settings->get('uhsg_sisu_sslkey_path', self::GRAPHQL_SSLKEY_PATH),
      'headers' => [
        'Content-Type: application/json',
        'client-app-id: doo-sg-web1-16.student.helsinki.fi',
      ],
    ];

    if (isset($data)) {
      $options['body'] = $data;
    }

    return $options;
  }

  /**
   * Handles the response.
   *
   * @param \Psr\Http\Message\ResponseInterface $response
   *   Response object.
   *
   * @return \Psr\Http\Message\ResponseInterface
   *   Response object.
   */
  private function handleResponse(ResponseInterface $response) {
    $this->logResponse($response);

    return $response;
  }

  /**
   * Logs the response object.
   *
   * @param \Psr\Http\Message\ResponseInterface $response
   *   Response object.
   */
  private function logResponse(ResponseInterface $response) {
    $responseCode = $response->getStatusCode();
    $responseData = $response->getBody()->getContents();

    if (in_array($responseCode, [200, 404])) {
      $this->log('Response: @code @data', [
        '@code' => $responseCode,
        '@data' => $responseData,
      ]);
    }
    else {
      $this->log('Response: @response', [
        '@response' => print_r($response, TRUE),
      ], RfcLogLevel::WARNING);
    }
  }

  /**
   * Logger.
   *
   * @see LoggerInterface::log()
   */
  private function log($message, $context = [], $severity = RfcLogLevel::NOTICE) {
    $this->loggerFactory->get('uhsg_sisu')->log($severity, $message, $context);
  }
}
