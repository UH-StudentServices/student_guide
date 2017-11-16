<?php
 
namespace Drupal\uhsg_office_hours;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\Logger\LoggerChannel;
use Drupal\uhsg_active_degree_programme\ActiveDegreeProgrammeService;
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

  /** @var LoggerChannel */
  protected $logger;

  /** @var TimeInterface */
  protected $time;

  /** @var ActiveDegreeProgrammeService */
  protected $activeDegreeProgrammeService;

  /** @var array */
  private $officeHourProperties = ['description', 'additionalInfo', 'location'];

  public function __construct(
    CacheBackendInterface $cache,
    Client $client,
    ConfigFactory $configFactory,
    LoggerChannel $logger,
    TimeInterface $time,
    ActiveDegreeProgrammeService $activeDegreeProgrammeService) {

    $this->cache = $cache;
    $this->client = $client;
    $this->config = $configFactory->get(self::CONFIG_NAME);
    $this->logger = $logger;
    $this->time = $time;
    $this->activeDegreeProgrammeService = $activeDegreeProgrammeService;
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
        } catch (\Exception $e) {
          $this->logger->error($e->getMessage());
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
      $responseBody = '[
                {
                    "name": "Olli Opettaja",
                    "officeHours": [
                        {
                            "additionalInfo": "Lisätieto 1",
                            "degreeProgrammes": [
                                "KH40_002"
                            ],
                            "description": "Tue 14-15",
                            "location": "ART-CENTRE-105"
                        },
                        {
                            "additionalInfo": "Taideaika 2",
                            "degreeProgrammes": [
                                "KH40_002"
                            ],
                            "description": "Wed 14-15",
                            "location": "ART-CENTRE-105"
                        },
                        {
                            "additionalInfo": "T\u00e4ss\u00e4 on biologia ja kemia",
                            "degreeProgrammes": [
                                "KH57_001",
                                "KH50_003"
                            ],
                            "description": "Maanantaisin 12-15",
                            "location": null
                        }
                    ]
                },
                {
                    "name": "Matti Meikäläinen",
                    "officeHours": [
                        {
                            "additionalInfo": "Matin lisätieto 1",
                            "degreeProgrammes": [
                                "KH40_002"
                            ],
                            "description": "Mon 08-09",
                            "location": "Matinpaikka 15"
                        },
                        {
                            "additionalInfo": "Matin lisätieto 2",
                            "degreeProgrammes": [
                                "KH40_002"
                            ],
                            "description": "Sat 12-14",
                            "location": "Matinpaikka 123"
                        }
                    ]
                },
                {
                    "name": "Kieli Mielinen",
                    "officeHours": [
                        {
                            "additionalInfo": "Kielilisätieto 1",
                            "degreeProgrammes": [],
                            "description": "Wed 12-13",
                            "location": "Kielikeskus 1"
                        },
                        {
                            "additionalInfo": "Kielilisätieto 2",
                            "description": "thu 17-18",
                            "location": "Kielikeskus 2"
                        },
                        {
                            "additionalInfo": "Kielilisätieto 3",
                            "degreeProgrammes": [],
                            "description": "Maanantaisin 13-15",
                            "location": null
                        }
                    ]
                },
                {
                    "name": "Kielten Opettaja",
                    "officeHours": [
                        {
                            "additionalInfo": "Kielilisätieto XXX",
                            "degreeProgrammes": [],
                            "description": "Wed 15-19",
                            "location": "Kielikeskus 123"
                        },
                        {
                            "additionalInfo": "Kielilisätieto ABC",
                            "description": "Fri 09-10",
                            "location": "Kielikeskus 222"
                        }
                    ]
                }
            ]';
      $decodedBody = json_decode($responseBody);
      if (is_array($decodedBody)) {
        $restructuredOfficeHours = $this->restructureOfficeHours($decodedBody);
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
   *   Return office hours grouped by degree programme specifics and general
   *   ones. All persons' office hours are merged.
   */
  private function restructureOfficeHours(array $decodedBody) {
    $officeHours = [];
    if (!empty($decodedBody)) {
      $officeHours['degree_programme'] = [];
      $officeHours['general'] = [];

      foreach ($decodedBody as $person) {
        foreach ($person->officeHours as $personsOfficeHours) {
          if (empty($personsOfficeHours->degreeProgrammes)) {
            $officeHours['general'][] = [
              'name' => $person->name,
              'hours' => $this->mergeContents($personsOfficeHours),
            ];
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
    $this->cache->set(self::CACHE_KEY, $officeHours, $this->getCacheExpireTimestamp());
  }

  /**
   * @return int
   */
  private function getCacheExpireTimestamp() {
    return $this->time->getRequestTime() + self::CACHE_EXPIRE_SECONDS;
  }
}
