<?php

namespace Drupal\uhsg_sisu\Services;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Component\Serialization\SerializationInterface;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Site\Settings;

/**
 * Class SisuService.
 *
 * @package Drupal\uhsg_sisu\Service
 */
class SisuService {

  /**
   * Enable debug log.
   *
   * @var bool
   */
  const DEBUG = TRUE;

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
  const GRAPHQL_CERT_PATH = '/app/keys/esb/qa/esb.crt';

  /**
   * Default SSLKEY path.
   *
   * @var string
   */
  const GRAPHQL_SSLKEY_PATH = '/app/keys/esb/qa/esb.key';

  /**
   * Course realisation data cache lifetime in seconds.
   *
   * @var int
   */
  const COURSE_REALISATION_CACHE_LIFETIME = 300;

  /**
   * Drupal settings.
   *
   * @var \Drupal\Core\Site\Settings
   */
  private $settings;

  /**
   * Drupal\Core\Logger\LoggerChannelInterface definition.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  private $logger;

  /**
   * Drupal\Component\Serialization\SerializationInterface definition.
   *
   * @var \Drupal\Component\Serialization\SerializationInterface
   */
  private $jsonSerialization;

  /**
   * Drupal\Core\Cache\CacheBackendInterface definition.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  private $cache;

  /**
   * Drupal\Component\Datetime\TimeInterface definition.
   *
   * @var \Drupal\Component\Datetime\TimeInterface
   */
  private $time;

  /**
   * Service constructor.
   *
   * @param \Drupal\Core\Site\Settings $settings
   *   The Drupal settings.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $loggerChannelFactory
   *   The logger factory.
   * @param \Drupal\Component\Serialization\SerializationInterface $jsonSerialization
   *   The JSON serializer.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache
   *   The cache backend.
   * @param \Drupal\Component\Datetime\TimeInterface $time
   *   The Drupal time object.
   */
  public function __construct(Settings $settings, LoggerChannelFactoryInterface $loggerChannelFactory, SerializationInterface $jsonSerialization, CacheBackendInterface $cache, TimeInterface $time) {
    $this->settings = $settings;
    $this->logger = $loggerChannelFactory->get('uhsg_sisu');
    $this->jsonSerialization = $jsonSerialization;
    $this->cache = $cache;
    $this->time = $time;
  }

  /**
   * Fetch studyrights for person.
   *
   * @param string $oodiUid
   *   Oodi user id.
   *
   * @return array|null
   *   JSON decoded data or NULL.
   */
  public function getStudyRights($oodiUid) {
    $query = [
      "operationName" => "getStudyRights",
      "variables" => [
        "ids" => [
          "hy-hlo-" . $oodiUid,
        ],
      ],
      "query" => 'query StudyRightsQuery($personId: ID!) {
        private_person(id: $personId) {
          studyRightPrimalityChain {
            studyRightPrimalities {
              studyRightId
              startDate
              endDate
              documentState
            }
          }
          studyRights {
            id
            studyRightGraduation {
              phase1GraduationDate
              phase2GraduationDate
            }
            acceptedSelectionPath {
              educationPhase1Child {
                code
                groupId
                name {fi sv en}
              }
              educationPhase1 {
                code
                groupId
                name {
                  fi
                  sv
                  en
                }
              }
              educationPhase2Child {
                code
                groupId
                name {fi sv en}
              }
              educationPhase2 {
                code
                groupId
                name {
                  fi
                  sv
                  en
                }
              }
            }
          }
        }
      }',
    ];

    return $this->apiRequest($query);
  }


  /**
   * Make a GraphQL api request.
   *
   * @param array $graphQlQuery
   *   The GraphQL query data.
   * @param bool $sign
   *   Should we sign the request, defaults to TRUE.
   *
   * @return array|null
   *   JSON decoded data array or NULL.
   */
  public function apiRequest(array $graphQlQuery, $sign = TRUE) {
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
      CURLOPT_URL => $this->settings->get('uhsg_sisu_graphql_url', self::GRAPHQL_URL),
      CURLOPT_POSTFIELDS => $this->jsonSerialization->encode($graphQlQuery),
    ];

    if ($sign) {
      // We need the HTTP(S) requests to be signed with service specific
      // certificates that are whitelisted for the ESB servers.
      // There are at least 3 firewall levels that need to be passed on these
      // requests:
      // 1. HY outer infra firewall (because of Silta hosting).
      // 2. iptables config in ESBM.
      // 3. Service specific software whitelistings.
      $options[CURLOPT_SSLCERT] = $this->settings->get('uhsg_sisu_cert_path', self::GRAPHQL_CERT_PATH);
      $options[CURLOPT_SSLKEY] = $this->settings->get('uhsg_sisu_sslkey_path', self::GRAPHQL_SSLKEY_PATH);
    }

    $ch = curl_init();
    curl_setopt_array($ch, $options);
    $result = curl_exec($ch);

    if (!$result) {
      $this->logger->error('GraphQL doo-sisu curl request failed: @error', [
        '@error' => curl_error($ch),
      ]);
    }

    // Debug log.
    if ($this->settings->get('uhsg_sisu_debug', self::DEBUG)) {
      $this->logger->notice('GraphQL doo-sisu posted query : @query', [
        '@query' => print_r($this->jsonSerialization->encode($graphQlQuery), TRUE),
      ]);

      $this->logger->notice('GraphQL doo-sisu curl result : @result', [
        '@result' => print_r($result, TRUE),
      ]);

      $this->logger->notice('GraphQL doo-sisu curl request data : @data', [
        '@data' => print_r(curl_getinfo($ch), TRUE),
      ]);
    }

    curl_close($ch);

    return $this->jsonSerialization->decode($result);
  }

}
