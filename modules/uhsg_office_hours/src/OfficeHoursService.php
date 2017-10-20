<?php
 
namespace Drupal\uhsg_office_hours;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannel;
use Drupal\taxonomy\TermInterface;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Psr\Http\Message\ResponseInterface;

class OfficeHoursService {

  const CACHE_EXPIRE_SECONDS = 60; // 1 minute.
  const CACHE_KEY = 'uhsg-office-hours';
  const CONFIG_NAME = 'uhsg_office_hours.config';
  const CONFIG_API_BASE_URL = 'api_base_url';
  const CONFIG_API_PATH = 'api_path';
  const CONNECT_TIMEOUT_SECONDS = 2;
  const REQUEST_TIMEOUT_SECONDS = 2;

  /** @var CacheBackendInterface */
  protected $cache;

  /** @var Client */
  protected $client;

  /** @var ImmutableConfig */
  protected $config;

  /** @var ConfigFactory */
  protected $configFactory;

  /** @var EntityTypeManagerInterface */
  protected $entityTypeManager;

  /** @var LoggerChannel */
  protected $logger;

  /** @var TimeInterface */
  protected $time;

  public function __construct(
    CacheBackendInterface $cache,
    Client $client,
    ConfigFactory $configFactory,
    EntityTypeManagerInterface $entityTypeManager,
    LoggerChannel $logger,
    TimeInterface $time) {

    $this->cache = $cache;
    $this->client = $client;
    $this->config = $configFactory->get(self::CONFIG_NAME);
    $this->entityTypeManager = $entityTypeManager;
    $this->logger = $logger;
    $this->time = $time;
  }

  /**
   * @return array
   */
  public function getOfficeHours() {
    $officeHours = [];
    $cachedOfficeHours = $this->getOfficeHoursFromCache();

    if ($cachedOfficeHours) {
      return $cachedOfficeHours;
    }

    $apiUrl = $this->getApiUrl();

    if (!empty($apiUrl)) {
      try {
        $apiResponse = $this->client->get($apiUrl, $this->getRequestOptions());
        $officeHours = $this->handleResponse($apiResponse);

        if (!empty($officeHours)) {
          $this->setOfficeHoursToCache($officeHours);
        }
      } catch (\Exception $e) {
        $this->logger->error($e->getMessage());
      }
    }

    return $officeHours;
  }

  /**
   * @return null|array
   */
  private function getOfficeHoursFromCache() {
    $officeHours = $this->cache->get(self::CACHE_KEY);

    return $officeHours ? $officeHours->data : NULL;
  }

  /**
   * @return null|string
   */
  private function getApiUrl() {
    $apiBaseUrl = $this->config->get(self::CONFIG_API_BASE_URL);
    $apiPath = $this->config->get(self::CONFIG_API_PATH);

    return isset($apiBaseUrl, $apiPath) ? $apiBaseUrl . $apiPath : NULL;
  }

  /**
   * @return array
   */
  private function getRequestOptions() {
    return [
      RequestOptions::CONNECT_TIMEOUT => self::CONNECT_TIMEOUT_SECONDS,
      RequestOptions::TIMEOUT => self::REQUEST_TIMEOUT_SECONDS
    ];
  }

  /**
   * @param ResponseInterface $apiResponse
   * @return array
   */
  private function handleResponse(ResponseInterface $apiResponse) {
    if ($apiResponse->getStatusCode() == 200) {
      $responseBody = $apiResponse->getBody();
      $decodedBody = json_decode($responseBody);

      if (is_array($decodedBody) && !empty($decodedBody)) {
        $officeHours = [];
        $officeHours['degree_programme'] = [];
        $officeHours['general'] = [];
        $degreeProgrammeCodeTermIdMap = $this->getDegreeProgrammeCodeTermIdMap();

        foreach ($decodedBody as $officeHour) {
          $degreeProgrammeCodes = isset($officeHour->degreeProgrammes) ? $officeHour->degreeProgrammes : [];
          $degreeProgrammeTermIds = $this->mapDegreeProgrammeCodesToTermIds($degreeProgrammeCodes, $degreeProgrammeCodeTermIdMap);

          if (empty($degreeProgrammeCodes)) {
            $officeHours['general'][] = [
              'name' => $officeHour->name,
              'hours' => $officeHour->officeHours
            ];
          }
          else {
            $officeHours['degree_programme'][] = [
              'name' => $officeHour->name,
              'hours' => $officeHour->officeHours,
              'degree_programme_term_ids' => implode(',', $degreeProgrammeTermIds),
            ];
          }
        }
      }
    }

    return isset($officeHours) ? $officeHours : [];
  }

  /**
   * @return array
   */
  private function getDegreeProgrammeCodeTermIdMap() {
    $degreeProgrammeTerms = $this->loadAllDegreeProgrammeTerms();
    $degreeProgrammeCodeTermIdMap = [];

    foreach ($degreeProgrammeTerms as $term) {
      $code = $term->get('field_code')->value;
      $termId = $term->id();
      $degreeProgrammeCodeTermIdMap[$code] = $termId;
    }

    return $degreeProgrammeCodeTermIdMap;
  }

  /**
   * @return TermInterface[]
   */
  private function loadAllDegreeProgrammeTerms() {
    return $this->entityTypeManager->getStorage('taxonomy_term')->loadTree('degree_programme', 0, NULL, TRUE);
  }

  /**
   * @param array $degreeProgrammeCodes
   * @param array $degreeProgrammeCodeTermIdMap
   * @return array
   */
  private function mapDegreeProgrammeCodesToTermIds($degreeProgrammeCodes, array $degreeProgrammeCodeTermIdMap) {
    $degreeProgrammeTermIds = [];

    if (!empty($degreeProgrammeCodes)) {
      foreach ($degreeProgrammeCodes as $degreeProgrammeCode) {
        if (isset($degreeProgrammeCodeTermIdMap[$degreeProgrammeCode])) {
          $degreeProgrammeTermIds[] = $degreeProgrammeCodeTermIdMap[$degreeProgrammeCode];
        }
      }
    }

    return $degreeProgrammeTermIds;
  }

  /**
   * @param array $officeHours
   */
  private function setOfficeHoursToCache(array $officeHours) {
    $this->cache->set(self::CACHE_KEY, $officeHours, $this->getCacheExpireTimestamp());
  }

  /**
   * @return int
   */
  private function getCacheExpireTimestamp() {
    return $this->time->getRequestTime() + self::CACHE_EXPIRE_SECONDS;
  }
}
