<?php

namespace Drupal\uhsg_oprek\Oprek;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\uhsg_oprek\Oprek\StudyRight\StudyRight;
use GuzzleHttp\Client;

/**
 * Service for interacting with backend integration service "Oprek".
 */
class OprekService implements OprekServiceInterface {

  /**
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected $config;

  /**
   * @var \GuzzleHttp\Client
   */
  protected $client;

  /**
   * OprekService constructor.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config
   * @param \GuzzleHttp\Client $client
   */
  public function __construct(ConfigFactoryInterface $config, Client $client) {
    $this->config = $config->get('uhsg_oprek.settings');
    $this->client = $client;
  }

  /**
   * {@inheritdoc}
   */
  public function getVersion() {
    $body = $this->get('/version');
    if (!empty($body['version'])) {
      return (string) $body['version'];
    }
    return '';
  }

  /**
   * {@inheritdoc}
   */
  public function getStudyRights($studentNumber) {
    if (!is_string($studentNumber)) {
      throw new \InvalidArgumentException('Student number must be string type.');
    }
    $body = $this->get('/students/:student_number/studyrights', [':student_number' => $studentNumber]);
    if (!empty($body) && is_array($body)) {
      $return = [];
      foreach ($body as $study_right) {
        $return[] = new StudyRight($study_right);
      }
      return $return;
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

    $response = $this->client->get($this->config->get('base_url') . $uri, ['cert' => $this->config->get('cert_filepath'), 'ssl_key' => $this->config->get('cert_key_filepath')]);
    if ($response->getStatusCode() == 200) {
      $body = Json::decode($response->getBody()->getContents());
      if ($this->getStatusFromBody($body) == 200) {
        return $this->getDataFromBody($body);
      }
      else {
        throw new \Exception('Oprek service responded, but body status is not OK', ($response->getStatusCode() * 1000) + $this->getStatusFromBody($body));
      }
    }
    else {
      throw new \Exception('Oprek service did not responded OK', $response->getStatusCode() * 1000);
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
