<?php
 
namespace Drupal\uhsg_office_hours;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\taxonomy\TermInterface;
use GuzzleHttp\Client;

class OfficeHoursService {

  /** @var CacheBackendInterface */
  protected $cache;

  /** @var Client */
  protected $client;

  /** @var EntityTypeManagerInterface */
  protected $entityTypeManager;

  public function __construct(Client $client, CacheBackendInterface $cache, EntityTypeManagerInterface $entityTypeManager) {
    $this->client = $client;
    $this->cache = $cache;
    $this->entityTypeManager = $entityTypeManager;
  }

  public function getOfficeHours() {
    // TODO: Call the real endpoint when it is ready.
    //$apiResponse = $this->client->get('http://www.example.com');

    // Test:
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
      $degreeProgrammeCodes = $officeHour->degreeProgrammes;
      $degreeProgrammeTids = [];

      foreach ($degreeProgrammeCodes as $degreeProgrammeCode) {
        if (isset($degreeProgrammeCodeTidMap[$degreeProgrammeCode])) {
          $degreeProgrammeTids[] = $degreeProgrammeCodeTidMap[$degreeProgrammeCode];
        }
      }

      $officeHours[] = [
        'name' => $officeHour->name,
        'hours' => $officeHour->officeHours,
        'degree_programme_tids' => implode(',', $degreeProgrammeTids),
      ];
    }

    return isset($officeHours) ? $officeHours : [];
  }

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
}
