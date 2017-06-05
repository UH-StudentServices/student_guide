<?php
 
namespace Drupal\uhsg_office_hours;

use Drupal\Core\Cache\CacheBackendInterface;
use GuzzleHttp\Client;

class OfficeHoursService {

  /** @var Client */
  protected $client;

  public function __construct(Client $client, CacheBackendInterface $cache) {
    $this->client = $client;
  }

  public function getOfficeHours() {
    // TODO: Call the real endpoint when it is ready.
    //$this->client->get('http://www.example.com');

    // TODO: Use the real data. These are for testing.
    $officeHours = [
      ['name' => 'Teacher 1', 'hours' => 'My office hours', 'degree_programme_code' => 'ABC'],
      ['name' => 'Teacher 2', 'hours' => 'My office hours', 'degree_programme_code' => 'DEF'],
      ['name' => 'Teacher 3', 'hours' => 'My office hours', 'degree_programme_code' => 'GHI'],
      ['name' => 'Teacher 4', 'hours' => 'My office hours', 'degree_programme_code' => 'JKL'],
    ];

    return $officeHours;
  }
}
