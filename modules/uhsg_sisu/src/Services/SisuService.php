<?php

namespace Drupal\uhsg_sisu\Services;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Logger\RfcLogLevel;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Site\Settings;
use Drupal\Core\Config\ConfigFactory;
use Psr\Http\Message\ResponseInterface;

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
  const GRAPHQL_CERT_PATH = '/etc/ssl/certs/esb/doo-sg-web1-16.student.helsinki.fi.pem';

  /**
   * Default SSLKEY path.
   *
   * @var string
   */
  const GRAPHQL_SSLKEY_PATH = '/etc/ssl/certs/esb/doo-sg-web1-16.student.helsinki.fi.key';

  /**
   * Logger Factory.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  private $loggerFactory;

  /**
   * Config.
   *
   * @var \Drupal\Core\Config\ConfigFactory
   */
  private $config;

  /**
   * Service constructor.
   *
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $loggerFactory
   *   LoggerChannelFactory.
   * @param \Drupal\Core\Config\ConfigFactory $config
   *   Config.
   */
  public function __construct(LoggerChannelFactoryInterface $loggerFactory,
                              ConfigFactory $config) {
    $this->loggerFactory = $loggerFactory;
    $this->config = $config->get('uhsg_sisu.settings');

  }

  /**
   * Get api url.
   *
   * @return string
   *   The absolute API url as a string.
   */
  public function getGraphQlUrl() {
    return $this->config->get('uhsg_sisu_graphql_url', self::GRAPHQL_URL);
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
    // Request and return.
    return $this->request($this->getGraphQlUrl(), 'POST', $graphQlQuery);
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

    // Initialize Curl
    $ch = curl_init();
    // Set curl options
    curl_setopt_array($ch, $options);
    // Fetch results
    $result = curl_exec($ch);

    // Log errors if we have no results
    if (!$result) {
      $this->log('SisuService curl request failed: @error', [
        '@error' => curl_error($ch),
      ], RfcLogLevel::WARNING);
    }

    curl_close($ch);

    return Json::decode($result);
  }

  /**
   * Constructs and returns request options.
   *
   * @param null|mixed $graphQlQuery
   *   Optional request graphQlQuery.
   *
   * @return array
   *   Request options.
   */
  private function getRequestOptions($graphQlQuery) {
    $options = [
      CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
      ],
      // GraphQL expects POST requests with payload.
      CURLOPT_POST => 1,
      // Pass headers to the data stream.
      CURLOPT_HEADER => 0,
      // Force new connection. Might avoid mystery.
      CURLOPT_FRESH_CONNECT => 1,
      // TRUE to return the transfer as a string of the return value of
      // curl_exec() instead of outputting it directly.
      CURLOPT_RETURNTRANSFER => 1,
      // Timeout increased 4->10.
      CURLOPT_TIMEOUT => 10,
      // Debugging help.
      CURLOPT_VERBOSE => TRUE,
      // https://curl.haxx.se/libcurl/c/CURLOPT_URL.html.
      CURLOPT_URL => $this->getGraphQlUrl(),
      // Encode post data
      CURLOPT_POSTFIELDS => Json::encode($graphQlQuery),
      // Sign our requests properly.
      CURLOPT_SSLCERT => $this->config->get('uhsg_sisu_cert_path', self::GRAPHQL_CERT_PATH),
      CURLOPT_SSLKEY => $this->config->get('uhsg_sisu_sslkey_path', self::GRAPHQL_SSLKEY_PATH),
    ];

    return $options;
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
