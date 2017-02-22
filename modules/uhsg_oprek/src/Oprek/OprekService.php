<?php

namespace Drupal\uhsg_oprek\Oprek;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Config\ConfigFactoryInterface;
use GuzzleHttp\Client;

class OprekService implements OprekServiceInterface {

  /**
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected $config;

  /**
   * @var Client
   */
  protected $client;

  /**
   * OprekService constructor.
   * @param ConfigFactoryInterface $config
   * @param Client $client
   */
  public function __construct(ConfigFactoryInterface $config, Client $client) {
    $this->config = $config->get('uhsg_oprek.settings');
    $this->client = $client;
  }

  /**
   * Gets the version of the backend service.
   * @return string
   */
  public function getVersion() {
    $body = $this->get('/version');
    if (!empty($body->version)) {
      return (string) $body->version;
    }
    return '';
  }

  /**
   * Gets study rights of given student number.
   * @param $studentNumber
   * @return object
   */
  public function getStudyRights($studentNumber) {
    if (!is_string($studentNumber)) {
      throw new \InvalidArgumentException('Student number must be string type.');
    }
    $body = $this->get('/students/:student_number/studyrights', [':student_number' => $studentNumber]);
    if (!empty($body)) {
      return (object) $body;
    }
    return (object) [];
  }

  /**
   * Returns the elements of study rights.
   * @param $studyRights
   * @return array
   */
  public function getStudyRightElements($studyRights) {
    if (!is_object($studyRights)) {
      throw new \InvalidArgumentException('Study rights argument must be an object.');
    }
    if (!empty($studyRights->elements)) {
      return (array) $studyRights->elements;
    }
    return [];
  }

  /**
   * Performs an GET request against the service.
   * @param $uri
   * @param array $parameters
   * @return mixed
   * @throws \Exception
   */
  protected function get($uri, $parameters = []) {

    // Set parameters to URI
    foreach ($parameters as $key => $value) {
      $uri = str_replace($key, $value, $uri);
    }

    $response = $this->client->get($this->config->get('base_url') . $uri);
    if ($response->getStatusCode() == 200) {
      $body = Json::decode($response->getBody()->getContents());
      if ($this->getStatusFromBody($body) == 200) {
        return $this->getDataFromBody($body);
      }
      else {
        throw new \Exception('Oprek service responded, but body status is not OK', ($response->getStatusCode()*1000)+$this->getStatusFromBody($body));
      }
    }
    else {
      throw new \Exception('Oprek service did not responded OK', $response->getStatusCode()*1000);
    }

  }

  /**
   * Gets status code from body.
   * @param $body
   * @return int
   * @throws \Exception
   */
  protected function getStatusFromBody($body) {
    if (!empty($body['status'])) {
      return (int) $body['status'];
    }
    throw new \Exception('Oprek service response status code is missing');
  }

  /**
   * Gets data payload from the body.
   * @param $body
   * @return array
   */
  protected function getDataFromBody($body) {
    if (!empty($body['data'])) {
      return $body['data'];
    }
    return [];
  }

}
