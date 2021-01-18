<?php

namespace Drupal\uhsg_sisu\Services;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Logger\RfcLogLevel;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Site\Settings;
use Psr\Http\Message\ResponseInterface;

/**
 * Class SisuService.
 *
 * To switch the authentication method from API GW->ESB to direct ESB,
 * one can simply replace the cert and path in settings.local.php :
 *   $settings['uhsg_sisu_graphql_url'] = 'https://esbmt2.it.helsinki.fi/secure/doo-sisu/graphql'; // ESB QA
 *   $settings['uhsg_sisu_cert_path'] = '/etc/ssl/certs/esb/doo-sg-web1-16.student.helsinki.fi.pem'; // ESB, QA and PROD
 *   $settings['uhsg_sisu_sslkey_path'] = '/etc/ssl/certs/esb/doo-sg-web1-16.student.helsinki.fi.key'; // ESB, QA and PROD
 *   $settings['uhsg_sisu_apigw_api_key'] = ''; // APIGW only, QA/PROD separate
 *
 * @package Drupal\uhsg_sisu\Services\SisuService
 */
class SisuService {

  /**
   * Default API url.
   *
   * @var string
   */
  const GRAPHQL_URL = 'https://gw-api-test.it.helsinki.fi/secure/sisu/graphql';

  /**
   * Default CERT path.
   *
   * @var string
   */
  const GRAPHQL_CERT_PATH = '/etc/pki/tls/apigw/apigw.crt';

  /**
   * Default SSLKEY path.
   *
   * @var string
   */
  const GRAPHQL_SSLKEY_PATH = '/etc/pki/tls/apigw/apigw.key';

  /**
   * Default X-Api-Key (API GW only).
   *
   * @var string
   */
  const APIGW_API_KEY = '';


  /**
   * Logger Factory.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  private $loggerFactory;

  /**
   * Settings.
   *
   * @var \Drupal\Core\Settings
   */
  private $settings;

  /**
   * Service constructor.
   *
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $loggerFactory
   *   LoggerChannelFactory.
   * @param \Drupal\Core\Settings $settings
   *   Settings.
   */
  public function __construct(LoggerChannelFactoryInterface $loggerFactory,
                              Settings $settings) {
    $this->loggerFactory = $loggerFactory;
    $this->settings = $settings;

  }

  /**
   * Get api url.
   *
   * @return string
   *   The absolute API url as a string.
   */
  public function getGraphQlUrl() {
    return $this->settings->get('uhsg_sisu_graphql_url', self::GRAPHQL_URL);
  }

  /**
   * Gets information regarding the given realisation identified by ID.
   *
   * @param array $graphQlQuery
   *   Realisation ID.
   *
   * @return array
   *   Response array.
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
   * @return array
   *   Response array.
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
      CURLOPT_SSLCERT => $this->settings->get('uhsg_sisu_cert_path', self::GRAPHQL_CERT_PATH),
      CURLOPT_SSLKEY => $this->settings->get('uhsg_sisu_sslkey_path', self::GRAPHQL_SSLKEY_PATH),
    ];

    $api_key = $this->settings->get('uhsg_sisu_apigw_api_key', self::APIGW_API_KEY);
    if (!empty($api_key)) {
      $options[CURLOPT_HTTPHEADER][] = 'X-Api-Key: ' . $api_key;
    }


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
