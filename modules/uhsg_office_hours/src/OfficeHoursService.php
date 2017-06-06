<?php
 
namespace Drupal\uhsg_office_hours;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\taxonomy\TermInterface;
use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;

class OfficeHoursService {

  const CACHE_EXPIRE_SECONDS = 900; // 15 minutes.
  const CACHE_KEY = 'uhsg-office-hours';

  /** @var CacheBackendInterface */
  protected $cache;

  /** @var Client */
  protected $client;

  /** @var EntityTypeManagerInterface */
  protected $entityTypeManager;

  /** @var TimeInterface */
  protected $time;

  public function __construct(Client $client, CacheBackendInterface $cache, EntityTypeManagerInterface $entityTypeManager, TimeInterface $time) {
    $this->client = $client;
    $this->cache = $cache;
    $this->entityTypeManager = $entityTypeManager;
    $this->time = $time;
  }

  /**
   * @return array
   */
  public function getOfficeHours() {
    $cachedOfficeHours = $this->getOfficeHoursFromCache();

    if ($cachedOfficeHours) {
      return $cachedOfficeHours;
    }

    // TODO: Call the real endpoint when it is ready.
    $apiResponse = $this->client->get('http://www.example.com');
    $officeHours = $this->handleResponse($apiResponse);
    $this->setOfficeHoursToCache($officeHours);

    return $officeHours;
  }

  /**
   * @return array|NULL
   */
  private function getOfficeHoursFromCache() {
    $officeHours = $this->cache->get(self::CACHE_KEY);

    return $officeHours ? $officeHours->data : NULL;
  }

  /**
   * @param ResponseInterface $apiResponse
   * @return array
   */
  private function handleResponse(ResponseInterface $apiResponse) {
    if ($apiResponse->getStatusCode() == 200) {
      $responseBody = $apiResponse->getBody();

      // TODO: This is just a test:
      $responseBody = '[
        {
          "name": "Olli Opettaja",
          "officeHours": "Maanantaisin klo 8.00",
          "degreeProgrammes": ["KH10_001", "KH60_001"]
        },
        {
          "name": "Leila Lehtori",
          "officeHours": "Parillisten kuukausien kolmas torstai klo 11.15–11.45.",
          "degreeProgrammes": ["KH60_001"]
        }
      ]';

      $decodedBody = json_decode($responseBody);
      $degreeProgrammeCodeTidMap = $this->getDegreeProgrammeCodeTidMap();

      foreach ($decodedBody as $officeHour) {
        $degreeProgrammeTids = $this->mapDegreeProgrammeCodesToTids($officeHour->degreeProgrammes, $degreeProgrammeCodeTidMap);

        $officeHours[] = [
          'name' => $officeHour->name,
          'hours' => $officeHour->officeHours,
          'degree_programme_tids' => implode(',', $degreeProgrammeTids),
        ];
      }
    }

    return isset($officeHours) ? $officeHours : [];
  }

  /**
   * @return array
   */
  private function getDegreeProgrammeCodeTidMap() {
    $degreeProgrammeTerms = $this->loadAllDegreeProgrammeTerms();
    $degreeProgrammeCodeTidMap = [];

    foreach ($degreeProgrammeTerms as $term) {
      $code = $term->get('field_code')->value;
      $tid = $term->id();
      $degreeProgrammeCodeTidMap[$code] = $tid;
    }

    return $degreeProgrammeCodeTidMap;
  }

  /**
   * @return TermInterface[]
   */
  private function loadAllDegreeProgrammeTerms() {
    return $this->entityTypeManager->getStorage('taxonomy_term')->loadTree('degree_programme', 0, NULL, TRUE);
  }

  /**
   * @param array $degreeProgrammeCodes
   * @param array $degreeProgrammeCodeTidMap
   * @return array
   */
  private function mapDegreeProgrammeCodesToTids(array $degreeProgrammeCodes, array $degreeProgrammeCodeTidMap) {
    $degreeProgrammeTids = [];

    foreach ($degreeProgrammeCodes as $degreeProgrammeCode) {
      if (isset($degreeProgrammeCodeTidMap[$degreeProgrammeCode])) {
        $degreeProgrammeTids[] = $degreeProgrammeCodeTidMap[$degreeProgrammeCode];
      }
    }

    return $degreeProgrammeTids;
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
