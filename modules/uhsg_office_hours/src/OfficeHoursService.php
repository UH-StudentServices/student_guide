<?php

namespace Drupal\uhsg_office_hours;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Logger\LoggerChannel;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\uhsg_active_degree_programme\ActiveDegreeProgrammeService;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Psr\Http\Message\ResponseInterface;

class OfficeHoursService {

  use StringTranslationTrait;

  const CACHE_KEY_PREFIX = 'uhsg-office-hours-';
  const CONFIG_NAME = 'uhsg_office_hours.config';
  const CONFIG_API_BASE_URL = 'api_base_url';
  const CONFIG_API_PATH = 'api_path';
  const CONFIG_CONNECT_TIMEOUT = 'connect_timeout';
  const CONFIG_REQUEST_TIMEOUT = 'request_timeout';
  const LANGUAGE_UNDEFINED = 'undefined';

  /** @var \Drupal\Core\Cache\CacheBackendInterface*/
  protected $cache;

  /** @var \GuzzleHttp\Client*/
  protected $client;

  /** @var \Drupal\Core\Config\ImmutableConfig*/
  protected $config;

  /** @var \Drupal\Core\Config\ConfigFactory*/
  protected $configFactory;

  /** @var \Drupal\Core\Language\LanguageManagerInterface*/
  protected $languageManager;

  /** @var \Drupal\Core\Logger\LoggerChannel*/
  protected $logger;

  /** @var \Drupal\Core\Messenger\MessengerInterface*/
  protected $messenger;

  /** @var \Drupal\Component\Datetime\TimeInterface*/
  protected $time;

  /** @var \Drupal\uhsg_active_degree_programme\ActiveDegreeProgrammeService*/
  protected $activeDegreeProgrammeService;

  /** @var array*/
  private $officeHourProperties = ['description', 'additionalInfo', 'location'];

  public function __construct(
    CacheBackendInterface $cache,
    Client $client,
    ConfigFactory $configFactory,
    LoggerChannel $logger,
    TimeInterface $time,
    ActiveDegreeProgrammeService $activeDegreeProgrammeService,
    LanguageManagerInterface $languageManager,
    MessengerInterface $messenger) {

    $this->cache = $cache;
    $this->client = $client;
    $this->config = $configFactory->get(self::CONFIG_NAME);
    $this->logger = $logger;
    $this->time = $time;
    $this->activeDegreeProgrammeService = $activeDegreeProgrammeService;
    $this->languageManager = $languageManager;
    $this->messenger = $messenger;
  }

  /**
   * @return array
   */
  public function getOfficeHours() {

    // Try to fetch from cache
    $officeHoursResponse = $this->getOfficeHoursFromCache();
    if (!$officeHoursResponse) {
      // Fetch from external API
      $apiUrl = $this->getApiUrl();
      $officeHoursResponse = [];
      if (!empty($apiUrl)) {
        try {
          $apiResponse = $this->client->get($apiUrl, $this->getRequestOptions());
          $officeHoursResponse = $this->handleResponse($apiResponse);
          if (!empty($officeHoursResponse)) {
            $this->setOfficeHoursToCache($officeHoursResponse);
          }
        }
        catch (\Exception $e) {
          $this->logger->error($e->getMessage());
          $this->messenger->addError($this->t('The office hours cannot be displayed. Please try again later.'));
        }
      }
    }

    // Filter office hours by active degree programme
    $filteredOfficeHours = $this->filterByActiveDegreeProgramme($officeHoursResponse);

    return $filteredOfficeHours;
  }

  /**
   * @return null|array
   */
  private function getOfficeHoursFromCache() {
    $officeHours = $this->cache->get($this->getCacheKey());

    return $officeHours ? $officeHours->data : NULL;
  }

  /**
   * @return string
   */
  private function getCacheKey() {
    return self::CACHE_KEY_PREFIX . $this->getCurrentLanguage();
  }

  /**
   * @return string
   */
  private function getCurrentLanguage() {
    return $this->languageManager->getCurrentLanguage()->getId();
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
      RequestOptions::CONNECT_TIMEOUT => $this->config->get(self::CONFIG_CONNECT_TIMEOUT),
      RequestOptions::TIMEOUT => $this->config->get(self::CONFIG_REQUEST_TIMEOUT)
    ];
  }

  /**
   * @param \Psr\Http\Message\ResponseInterface $apiResponse
   * @return array
   */
  private function handleResponse(ResponseInterface $apiResponse) {
    if ($apiResponse->getStatusCode() == 200) {
      $responseBody = $apiResponse->getBody();
      $decodedBody = json_decode($responseBody);
      if (is_array($decodedBody)) {
        $restructuredOfficeHours = $this->restructureOfficeHours($decodedBody);
        $this->sortGeneralOfficeHoursByLanguage($restructuredOfficeHours);
      }
      else {
        $this->logger->warning('Office hours API response was not in expected format (array).');
      }
    }

    return isset($restructuredOfficeHours) ? $restructuredOfficeHours : [];
  }

  /**
   * @param array $decodedBody
   *
   * @return array
   *   Return office hours grouped by degree programme specific hours and
   *   general office hours. General office hours are further grouped by
   *   language (if exists). All persons' office hours are merged.
   */
  private function restructureOfficeHours(array $decodedBody) {
    $officeHours = [];
    if (!empty($decodedBody)) {
      $officeHours['degree_programme'] = [];
      $officeHours['general'] = [];

      foreach ($decodedBody as $person) {
        foreach ($person->officeHours as $personsOfficeHours) {
          if (empty($personsOfficeHours->degreeProgrammes)) {
            if (empty($personsOfficeHours->languages)) {
              $officeHours['general'][self::LANGUAGE_UNDEFINED][] = [
                'name' => $person->name,
                'hours' => $this->mergeContents($personsOfficeHours),
              ];
            }
            else {
              foreach ($personsOfficeHours->languages as $language) {
                $officeHours['general'][$language->code][] = [
                  'name' => $person->name,
                  'hours' => $this->mergeContents($personsOfficeHours),
                  'language' => $language
                ];
              }
            }
          }
          else {
            $officeHours['degree_programme'][] = [
              'name' => $person->name,
              'hours' => $this->mergeContents($personsOfficeHours),
              'degree_programmes' => $personsOfficeHours->degreeProgrammes,
            ];
          }
        }
      }
    }

    return $officeHours;
  }

  /**
   * Sorts general office hours by language name using the current UI language.
   *
   * @param array $officeHours
   */
  private function sortGeneralOfficeHoursByLanguage(&$officeHours) {
    $generalHours = $officeHours['general'];

    usort($generalHours, function ($a, $b) {
      $languageA = isset($a[0]['language']) ? $a[0]['language']->name->{$this->getCurrentLanguage()} : '';
      $languageB = isset($b[0]['language']) ? $b[0]['language']->name->{$this->getCurrentLanguage()} : '';

      if ($languageA == $languageB) {
        return 0;
      }

      return $languageA < $languageB ? -1 : 1;
    });

    $officeHours['general'] = $generalHours;
  }

  /**
   * @param array $restructuredOfficeHours
   *
   * @return array
   */
  private function filterByActiveDegreeProgramme(array $restructuredOfficeHours) {
    $activeDegreeProgramme = $this->activeDegreeProgrammeService->getTerm();
    if (is_null($activeDegreeProgramme)) {
      // When no active degree programme, then we don't show any filterable
      // office hours.
      $restructuredOfficeHours['degree_programme'] = [];
    }
    elseif (!empty($restructuredOfficeHours['degree_programme'])) {
      foreach ($restructuredOfficeHours['degree_programme'] as $key => $value) {
        $found = FALSE;
        foreach ($activeDegreeProgramme->field_code->getValue() as $fieldItem) {
          if (in_array($fieldItem['value'], $value['degree_programmes'])) {
            $found = TRUE;
            break;
          }
        }
        if (!$found) {
          unset($restructuredOfficeHours['degree_programme'][$key]);
        }
      }
    }
    return $restructuredOfficeHours;
  }

  /**
   * @param $officeHours
   *
   * @return string
   */
  private function mergeContents($officeHours) {
    $contents = [];
    foreach ($this->officeHourProperties as $property) {
      if (!empty($officeHours->{$property})) {
        $contents[] = $officeHours->{$property};
      }
    }
    return implode(', ', $contents);
  }

  /**
   * @param array $officeHours
   */
  private function setOfficeHoursToCache(array $officeHours) {
    $this->cache->set($this->getCacheKey(), $officeHours, $this->getCacheExpireTimestamp());
  }

  /**
   * @return int
   */
  private function getCacheExpireTimestamp() {
    return $this->time->getRequestTime() + $this->config->get('cache_expire');
  }

}
