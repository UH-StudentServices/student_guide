<?php

namespace Drupal\uhsg_oprek\Oprek;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\uhsg_oprek\Oprek\StudyRight\StudyRight;
use GuzzleHttp\Client;
use Drupal\Core\Site\Settings;

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
   * Use mock responses? This can be overridden in settings.local.php with:
   *   $settings['uhsg_oprek_use_mock_response'] = TRUE;
   *
   * @var bool
   */
  const UHSG_OPREK_USE_MOCK_RESPONSE = FALSE;

  /**
   * Use complex mock responses if any? This can be overridden in settings.local.php with:
   *   $settings['uhsg_oprek_use_mock_response_complex'] = FALSE;
   *
   * @var bool
   */
  const UHSG_OPREK_USE_MOCK_RESPONSE_COMPLEX = TRUE;

  /**
   * Add debug logging?
   * This can be overridden in settings.local.php with:
   *   $settings['uhsg_oprek_add_debug_logging'] = TRUE;
   *
   * @var bool
   */
  const UHSG_OPREK_ADD_DEBUG_LOGGING = FALSE;

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

    if (Settings::get('uhsg_oprek_use_mock_response', self::UHSG_OPREK_USE_MOCK_RESPONSE)) {
      return $this->getDataFromMockResponse();
    }else{
      $response = $this->client->get($this->config->get('base_url') . $uri, ['cert' => $this->config->get('cert_filepath'), 'ssl_key' => $this->config->get('cert_key_filepath')]);
      if ($response->getStatusCode() == 200) {
        $body = Json::decode($response->getBody()->getContents());
        if (in_array($this->getStatusFromBody($body), [200, 204])) {
          return $this->getDataFromBody($body);
        }
        else {
          throw new \Exception('Oprek service responded, but body status is not OK', ($response->getStatusCode() * 1000) + $this->getStatusFromBody($body) . ', body=' . $body);
        }
      }
      else {
        throw new \Exception('Oprek service did not responded OK', $response->getStatusCode() * 1000);
      }
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


  /**
   * Gets data payload from the body of a mock response.
   * @return array
   */
  protected function getDataFromMockResponse() {
    // Choose from simple or complex mock json. Both are
    // real life examples but with person_id anonymized.
    // In earlier versions recognizing the primary study right/subject
    // of the complex response failed in some cases.
    return $this->chooseMockResponse(Settings::get('uhsg_oprek_use_mock_response_complex', self::UHSG_OPREK_USE_MOCK_RESPONSE_COMPLEX));
  }

    /**
     * Choose a mock response from a simple or more complex option.
     * Both are real world cases with only person ID anymized.
     * @param $complex - if TRUE, using complex response.
     * @return array
     */
    protected function chooseMockResponse(bool $complex = TRUE) {
      $complexity = 'simple';
      if($complex){
        $complexity = 'complex';
        $mock_json = '{
          "md5": "686b62d963808b243679c7853e01fc3a",
          "status": 200,
          "elapsed": 0.025998623,
          "data": [
              {
                  "degree_date": null,
                  "duration_months": 83,
                  "extent_code": 1,
                  "person_id": 1500000,
                  "study_start_date": "2019-07-31T21:00:00.000Z",
                  "state": [
                      {
                          "langcode": "fi",
                          "text": "Ensisijainen"
                      },
                      {
                          "langcode": "sv",
                          "text": "Primär studierätt"
                      },
                      {
                          "langcode": "en",
                          "text": "Primary"
                      }
                  ],
                  "faculty_code": "H20",
                  "elements": [
                      {
                          "element_id": 10,
                          "code": "00351",
                          "start_date": "2019-07-31T21:00:00.000Z",
                          "end_date": "2026-07-30T21:00:00.000Z",
                          "name": [
                              {
                                  "langcode": "fi",
                                  "text": "Oikeusnotaarin tutkinto"
                              },
                              {
                                  "langcode": "sv",
                                  "text": "Rättsnotarie"
                              },
                              {
                                  "langcode": "en",
                                  "text": "Bachelor of Laws"
                              }
                          ]
                      },
                      {
                          "element_id": 15,
                          "code": "A2004",
                          "start_date": "2019-07-31T21:00:00.000Z",
                          "end_date": "2026-07-30T21:00:00.000Z",
                          "name": [
                              {
                                  "langcode": "fi",
                                  "text": "Valtioneuvoston asetus (794/2004) yliopistojen tutkinnoista"
                              },
                              {
                                  "langcode": "sv",
                                  "text": "Statsrådets förordning (794/2004) om universitetsexamina"
                              },
                              {
                                  "langcode": "en",
                                  "text": "Government Decree (794/2004) on University Degrees"
                              }
                          ]
                      },
                      {
                          "element_id": 20,
                          "code": "KH20_001",
                          "start_date": "2019-07-31T21:00:00.000Z",
                          "end_date": "2026-07-30T21:00:00.000Z",
                          "name": [
                              {
                                  "langcode": "fi",
                                  "text": "Oikeusnotaarin koulutusohjelma"
                              },
                              {
                                  "langcode": "sv",
                                  "text": "Utbildningsprogrammet för rättsnotarie"
                              },
                              {
                                  "langcode": "en",
                                  "text": "Bachelor\'s Programme in Law"
                              }
                          ]
                      },
                      {
                          "element_id": 30,
                          "code": "SH20_121",
                          "start_date": "2019-07-31T21:00:00.000Z",
                          "end_date": "2026-07-30T21:00:00.000Z",
                          "name": [
                              {
                                  "langcode": "fi",
                                  "text": "Oikeustieteen koulutus, Helsinki"
                              },
                              {
                                  "langcode": "sv",
                                  "text": "Utbildning i juridik, Helsinfors"
                              },
                              {
                                  "langcode": "en",
                                  "text": "Study programme in Law, Helsinki"
                              }
                          ]
                      }
                  ],
                  "admission_date": "2019-06-30T21:00:00.000Z",
                  "studyright_id": 131179515,
                  "end_date": "2026-07-30T21:00:00.000Z",
                  "duration_remaining_months": 70,
                  "priority": 1,
                  "start_date": "2019-07-31T21:00:00.000Z",
                  "extent": [
                      {
                          "langcode": "fi",
                          "text": "Alempi korkeakoulututkinto"
                      },
                      {
                          "langcode": "sv",
                          "text": "Lägre högskoleexamen"
                      },
                      {
                          "langcode": "en",
                          "text": "Bachelor\'s Degree"
                      }
                  ],
                  "organisation_name": [
                      {
                          "langcode": "fi",
                          "text": "Oikeustieteellinen tiedekunta"
                      },
                      {
                          "langcode": "sv",
                          "text": "Juridiska fakulteten"
                      },
                      {
                          "langcode": "en",
                          "text": "Faculty of Law"
                      }
                  ],
                  "cancel_date": null,
                  "organisation_code": "H20"
              },
              {
                  "degree_date": null,
                  "duration_months": 83,
                  "extent_code": 2,
                  "person_id": 1505239,
                  "study_start_date": null,
                  "state": [
                      {
                          "langcode": "fi",
                          "text": "Optio"
                      },
                      {
                          "langcode": "sv",
                          "text": "Option"
                      },
                      {
                          "langcode": "en",
                          "text": "Option"
                      }
                  ],
                  "faculty_code": null,
                  "elements": [
                      {
                          "element_id": 10,
                          "code": "YLTUTK",
                          "start_date": "2019-07-31T21:00:00.000Z",
                          "end_date": "2026-07-30T21:00:00.000Z",
                          "name": [
                              {
                                  "langcode": "fi",
                                  "text": "Ylempi korkeakoulututkinto"
                              },
                              {
                                  "langcode": "sv",
                                  "text": "Högre högskoleexamen"
                              },
                              {
                                  "langcode": "en",
                                  "text": "Second-cycle degree"
                              }
                          ]
                      },
                      {
                          "element_id": 15,
                          "code": "A2004",
                          "start_date": "2019-07-31T21:00:00.000Z",
                          "end_date": "2026-07-30T21:00:00.000Z",
                          "name": [
                              {
                                  "langcode": "fi",
                                  "text": "Valtioneuvoston asetus (794/2004) yliopistojen tutkinnoista"
                              },
                              {
                                  "langcode": "sv",
                                  "text": "Statsrådets förordning (794/2004) om universitetsexamina"
                              },
                              {
                                  "langcode": "en",
                                  "text": "Government Decree (794/2004) on University Degrees"
                              }
                          ]
                      }
                  ],
                  "admission_date": "2019-06-30T21:00:00.000Z",
                  "studyright_id": 131179516,
                  "end_date": "2026-07-30T21:00:00.000Z",
                  "duration_remaining_months": 70,
                  "priority": 6,
                  "start_date": "2019-07-31T21:00:00.000Z",
                  "extent": [
                      {
                          "langcode": "fi",
                          "text": "Ylempi korkeakoulututkinto"
                      },
                      {
                          "langcode": "sv",
                          "text": "Högre högskoleexamen"
                      },
                      {
                          "langcode": "en",
                          "text": "Master\'s Degree"
                      }
                  ],
                  "organisation_name": [
                      {
                          "langcode": "fi",
                          "text": "Oikeustieteellinen tiedekunta"
                      },
                      {
                          "langcode": "sv",
                          "text": "Juridiska fakulteten"
                      },
                      {
                          "langcode": "en",
                          "text": "Faculty of Law"
                      }
                  ],
                  "cancel_date": null,
                  "organisation_code": "H20"
              },
              {
                  "degree_date": null,
                  "duration_months": 9,
                  "extent_code": 9,
                  "person_id": 1505239,
                  "study_start_date": "2020-03-19T22:00:00.000Z",
                  "state": [
                      {
                          "langcode": "fi",
                          "text": "Toissijainen"
                      },
                      {
                          "langcode": "sv",
                          "text": "Sekundär"
                      },
                      {
                          "langcode": "en",
                          "text": "Secondary"
                      }
                  ],
                  "faculty_code": null,
                  "elements": [
                      {
                          "element_id": 0,
                          "code": "00997",
                          "start_date": "2020-03-19T22:00:00.000Z",
                          "end_date": "2020-12-28T22:00:00.000Z",
                          "name": [
                              {
                                  "langcode": "fi",
                                  "text": "Muu koulutus, joka ei johda tutkintoon"
                              },
                              {
                                  "langcode": "sv",
                                  "text": "Andra fristående studier"
                              },
                              {
                                  "langcode": "en",
                                  "text": "Other Non-Degree Studies"
                              }
                          ]
                      },
                      {
                          "element_id": 60,
                          "code": "AY99319",
                          "start_date": "2020-03-19T22:00:00.000Z",
                          "end_date": "2020-12-28T22:00:00.000Z",
                          "name": [
                              {
                                  "langcode": "fi",
                                  "text": "Avoin yo: Repetera svenska - Ruotsin perusrakenteiden ja sanaston kertausta (CEFR A2)"
                              },
                              {
                                  "langcode": "sv",
                                  "text": "Öppna uni: Förberedande kurs i svenska"
                              },
                              {
                                  "langcode": "en",
                                  "text": "Open uni: Remedial Course in Swedish"
                              }
                          ]
                      }
                  ],
                  "admission_date": "2020-03-19T22:00:00.000Z",
                  "studyright_id": 135342004,
                  "end_date": "2020-12-28T22:00:00.000Z",
                  "duration_remaining_months": 3,
                  "priority": 2,
                  "start_date": "2020-03-19T22:00:00.000Z",
                  "extent": [
                      {
                          "langcode": "fi",
                          "text": "Avoin yliopisto"
                      },
                      {
                          "langcode": "sv",
                          "text": "Öppet universitet"
                      },
                      {
                          "langcode": "en",
                          "text": "Open University"
                      }
                  ],
                  "organisation_name": [
                      {
                          "langcode": "fi",
                          "text": "Avoin yliopisto"
                      },
                      {
                          "langcode": "sv",
                          "text": "Öppna universitetet"
                      },
                      {
                          "langcode": "en",
                          "text": "Open University"
                      }
                  ],
                  "cancel_date": null,
                  "organisation_code": "9301"
              },
              {
                  "degree_date": null,
                  "duration_months": 8,
                  "extent_code": 9,
                  "person_id": 1505239,
                  "study_start_date": "2020-03-19T22:00:00.000Z",
                  "state": [
                      {
                          "langcode": "fi",
                          "text": "Toissijainen"
                      },
                      {
                          "langcode": "sv",
                          "text": "Sekundär"
                      },
                      {
                          "langcode": "en",
                          "text": "Secondary"
                      }
                  ],
                  "faculty_code": null,
                  "elements": [
                      {
                          "element_id": 0,
                          "code": "00997",
                          "start_date": "2020-03-19T22:00:00.000Z",
                          "end_date": "2020-12-03T22:00:00.000Z",
                          "name": [
                              {
                                  "langcode": "fi",
                                  "text": "Muu koulutus, joka ei johda tutkintoon"
                              },
                              {
                                  "langcode": "sv",
                                  "text": "Andra fristående studier"
                              },
                              {
                                  "langcode": "en",
                                  "text": "Other Non-Degree Studies"
                              }
                          ]
                      },
                      {
                          "element_id": 60,
                          "code": "AYKK-RUOIK",
                          "start_date": "2020-03-19T22:00:00.000Z",
                          "end_date": "2020-12-03T22:00:00.000Z",
                          "name": [
                              {
                                  "langcode": "fi",
                                  "text": "Avoin yo: Toisen kotimaisen kielen suullinen taito, ruotsi (CEFR B1)"
                              },
                              {
                                  "langcode": "sv",
                                  "text": "Öppna uni: Muntlig färdighet i andra inhemska språket, svensk (CEFR B1)"
                              },
                              {
                                  "langcode": "en",
                                  "text": "Open uni: Oral Skills in the Second National Language, Swedish (CEFR B1)"
                              }
                          ]
                      }
                  ],
                  "admission_date": "2020-03-19T22:00:00.000Z",
                  "studyright_id": 135422037,
                  "end_date": "2020-12-03T22:00:00.000Z",
                  "duration_remaining_months": 2,
                  "priority": 2,
                  "start_date": "2020-03-19T22:00:00.000Z",
                  "extent": [
                      {
                          "langcode": "fi",
                          "text": "Avoin yliopisto"
                      },
                      {
                          "langcode": "sv",
                          "text": "Öppet universitet"
                      },
                      {
                          "langcode": "en",
                          "text": "Open University"
                      }
                  ],
                  "organisation_name": [
                      {
                          "langcode": "fi",
                          "text": "Avoin yliopisto"
                      },
                      {
                          "langcode": "sv",
                          "text": "Öppna universitetet"
                      },
                      {
                          "langcode": "en",
                          "text": "Open University"
                      }
                  ],
                  "cancel_date": null,
                  "organisation_code": "9301"
              },
              {
                  "degree_date": null,
                  "duration_months": 10,
                  "extent_code": 9,
                  "person_id": 1505239,
                  "study_start_date": "2020-03-21T22:00:00.000Z",
                  "state": [
                      {
                          "langcode": "fi",
                          "text": "Toissijainen"
                      },
                      {
                          "langcode": "sv",
                          "text": "Sekundär"
                      },
                      {
                          "langcode": "en",
                          "text": "Secondary"
                      }
                  ],
                  "faculty_code": null,
                  "elements": [
                      {
                          "element_id": 0,
                          "code": "00997",
                          "start_date": "2020-03-21T22:00:00.000Z",
                          "end_date": "2021-02-20T22:00:00.000Z",
                          "name": [
                              {
                                  "langcode": "fi",
                                  "text": "Muu koulutus, joka ei johda tutkintoon"
                              },
                              {
                                  "langcode": "sv",
                                  "text": "Andra fristående studier"
                              },
                              {
                                  "langcode": "en",
                                  "text": "Other Non-Degree Studies"
                              }
                          ]
                      },
                      {
                          "element_id": 60,
                          "code": "AYON-300",
                          "start_date": "2020-03-21T22:00:00.000Z",
                          "end_date": "2021-02-20T22:00:00.000Z",
                          "name": [
                              {
                                  "langcode": "fi",
                                  "text": "Avoin yo: Tieteellisen kirjoittamisen perusteet"
                              },
                              {
                                  "langcode": "sv",
                                  "text": "Öppna uni: Grunderna i vetenskapligt skrivande"
                              },
                              {
                                  "langcode": "en",
                                  "text": "Open uni: Basics in Scientific Writing"
                              }
                          ]
                      }
                  ],
                  "admission_date": "2020-03-21T22:00:00.000Z",
                  "studyright_id": 135772235,
                  "end_date": "2021-02-20T22:00:00.000Z",
                  "duration_remaining_months": 5,
                  "priority": 2,
                  "start_date": "2020-03-21T22:00:00.000Z",
                  "extent": [
                      {
                          "langcode": "fi",
                          "text": "Avoin yliopisto"
                      },
                      {
                          "langcode": "sv",
                          "text": "Öppet universitet"
                      },
                      {
                          "langcode": "en",
                          "text": "Open University"
                      }
                  ],
                  "organisation_name": [
                      {
                          "langcode": "fi",
                          "text": "Avoin yliopisto"
                      },
                      {
                          "langcode": "sv",
                          "text": "Öppna universitetet"
                      },
                      {
                          "langcode": "en",
                          "text": "Open University"
                      }
                  ],
                  "cancel_date": null,
                  "organisation_code": "9301"
              },
              {
                  "degree_date": null,
                  "duration_months": 17,
                  "extent_code": 9,
                  "person_id": 1505239,
                  "study_start_date": "2020-04-25T21:00:00.000Z",
                  "state": [
                      {
                          "langcode": "fi",
                          "text": "Toissijainen"
                      },
                      {
                          "langcode": "sv",
                          "text": "Sekundär"
                      },
                      {
                          "langcode": "en",
                          "text": "Secondary"
                      }
                  ],
                  "faculty_code": null,
                  "elements": [
                      {
                          "element_id": 0,
                          "code": "00997",
                          "start_date": "2020-04-25T21:00:00.000Z",
                          "end_date": "2021-09-29T21:00:00.000Z",
                          "name": [
                              {
                                  "langcode": "fi",
                                  "text": "Muu koulutus, joka ei johda tutkintoon"
                              },
                              {
                                  "langcode": "sv",
                                  "text": "Andra fristående studier"
                              },
                              {
                                  "langcode": "en",
                                  "text": "Other Non-Degree Studies"
                              }
                          ]
                      },
                      {
                          "element_id": 60,
                          "code": "AYON-P212",
                          "start_date": "2020-04-25T21:00:00.000Z",
                          "end_date": "2021-09-29T21:00:00.000Z",
                          "name": [
                              {
                                  "langcode": "fi",
                                  "text": "Avoin yo: Immateriaalioikeus ja kuluttajaoikeus"
                              },
                              {
                                  "langcode": "sv",
                                  "text": "Öppna uni: Immaterialrätt och konsumenträtt"
                              },
                              {
                                  "langcode": "en",
                                  "text": "Open uni: Intellectual Property Law and Consumer Law"
                              }
                          ]
                      }
                  ],
                  "admission_date": "2020-04-25T21:00:00.000Z",
                  "studyright_id": 136047260,
                  "end_date": "2021-09-29T21:00:00.000Z",
                  "duration_remaining_months": 12,
                  "priority": 2,
                  "start_date": "2020-04-25T21:00:00.000Z",
                  "extent": [
                      {
                          "langcode": "fi",
                          "text": "Avoin yliopisto"
                      },
                      {
                          "langcode": "sv",
                          "text": "Öppet universitet"
                      },
                      {
                          "langcode": "en",
                          "text": "Open University"
                      }
                  ],
                  "organisation_name": [
                      {
                          "langcode": "fi",
                          "text": "Avoin yliopisto"
                      },
                      {
                          "langcode": "sv",
                          "text": "Öppna universitetet"
                      },
                      {
                          "langcode": "en",
                          "text": "Open University"
                      }
                  ],
                  "cancel_date": null,
                  "organisation_code": "9301"
              },
              {
                  "degree_date": null,
                  "duration_months": 5,
                  "extent_code": 9,
                  "person_id": 1505239,
                  "study_start_date": "2020-05-29T21:00:00.000Z",
                  "state": [
                      {
                          "langcode": "fi",
                          "text": "Toissijainen"
                      },
                      {
                          "langcode": "sv",
                          "text": "Sekundär"
                      },
                      {
                          "langcode": "en",
                          "text": "Secondary"
                      }
                  ],
                  "faculty_code": null,
                  "elements": [
                      {
                          "element_id": 0,
                          "code": "00997",
                          "start_date": "2020-05-29T21:00:00.000Z",
                          "end_date": "2020-10-30T22:00:00.000Z",
                          "name": [
                              {
                                  "langcode": "fi",
                                  "text": "Muu koulutus, joka ei johda tutkintoon"
                              },
                              {
                                  "langcode": "sv",
                                  "text": "Andra fristående studier"
                              },
                              {
                                  "langcode": "en",
                                  "text": "Other Non-Degree Studies"
                              }
                          ]
                      },
                      {
                          "element_id": 60,
                          "code": "AYOIK-J492",
                          "start_date": "2020-05-29T21:00:00.000Z",
                          "end_date": "2020-10-30T22:00:00.000Z",
                          "name": [
                              {
                                  "langcode": "fi",
                                  "text": "Avoin yo: Lääkintä- ja bio-oikeus"
                              },
                              {
                                  "langcode": "sv",
                                  "text": "Öppna uni: Medicinering- och biorätt"
                              },
                              {
                                  "langcode": "en",
                                  "text": "Open uni: Health and Biomedical Law"
                              }
                          ]
                      }
                  ],
                  "admission_date": "2020-05-29T21:00:00.000Z",
                  "studyright_id": 136481975,
                  "end_date": "2020-10-30T22:00:00.000Z",
                  "duration_remaining_months": 1,
                  "priority": 2,
                  "start_date": "2020-05-29T21:00:00.000Z",
                  "extent": [
                      {
                          "langcode": "fi",
                          "text": "Avoin yliopisto"
                      },
                      {
                          "langcode": "sv",
                          "text": "Öppet universitet"
                      },
                      {
                          "langcode": "en",
                          "text": "Open University"
                      }
                  ],
                  "organisation_name": [
                      {
                          "langcode": "fi",
                          "text": "Avoin yliopisto"
                      },
                      {
                          "langcode": "sv",
                          "text": "Öppna universitetet"
                      },
                      {
                          "langcode": "en",
                          "text": "Open University"
                      }
                  ],
                  "cancel_date": null,
                  "organisation_code": "9301"
              }
          ]
      }';
    }else{
      $mock_json = '{
          "md5": "1bfd0b8aaf367c20f36cc8040d546ed8",
          "status": 200,
          "elapsed": 0.027522776,
          "data": [
              {
                  "degree_date": "2017-01-15T22:00:00.000Z",
                  "duration_months": 53,
                  "extent_code": 1,
                  "person_id": 90000000,
                  "study_start_date": "2012-07-31T21:00:00.000Z",
                  "state": [
                      {
                          "langcode": "fi",
                          "text": "Suorittanut tutkinnon"
                      },
                      {
                          "langcode": "sv",
                          "text": "Avlagt examen"
                      },
                      {
                          "langcode": "en",
                          "text": "Graduated"
                      }
                  ],
                  "faculty_code": "H10",
                  "elements": [
                      {
                          "element_id": 10,
                          "code": "00849",
                          "start_date": "2012-07-31T21:00:00.000Z",
                          "end_date": "2017-01-15T22:00:00.000Z",
                          "name": [
                              {
                                  "langcode": "fi",
                                  "text": "Teologian kandidaatti"
                              },
                              {
                                  "langcode": "sv",
                                  "text": "Teologie kandidat"
                              },
                              {
                                  "langcode": "en",
                                  "text": "Bachelor of Theology"
                              }
                          ]
                      },
                      {
                          "element_id": 15,
                          "code": "A2004",
                          "start_date": "2012-07-31T21:00:00.000Z",
                          "end_date": "2017-01-15T22:00:00.000Z",
                          "name": [
                              {
                                  "langcode": "fi",
                                  "text": "Valtioneuvoston asetus (794/2004) yliopistojen tutkinnoista"
                              },
                              {
                                  "langcode": "sv",
                                  "text": "Statsrådets förordning (794/2004) om universitetsexamina"
                              },
                              {
                                  "langcode": "en",
                                  "text": "Government Decree (794/2004) on University Degrees"
                              }
                          ]
                      },
                      {
                          "element_id": 20,
                          "code": "120009",
                          "start_date": "2012-07-31T21:00:00.000Z",
                          "end_date": "2013-07-30T21:00:00.000Z",
                          "name": [
                              {
                                  "langcode": "fi",
                                  "text": "Kirkkojen ja yhteiskunnan teologisiin tehtäviin valmistava koulutusohjelma"
                              },
                              {
                                  "langcode": "sv",
                                  "text": "Utbildningsprogrammet för kyrkliga och samhälleliga teologiska uppgifter"
                              },
                              {
                                  "langcode": "en",
                                  "text": "Degree Programme in Theological Functions in Churches and Society"
                              }
                          ]
                      },
                      {
                          "element_id": 20,
                          "code": "120013",
                          "start_date": "2013-07-31T21:00:00.000Z",
                          "end_date": "2017-01-15T22:00:00.000Z",
                          "name": [
                              {
                                  "langcode": "fi",
                                  "text": "Koulun uskonnonopettajan tehtäviin valmistava koulutusohjelma"
                              },
                              {
                                  "langcode": "sv",
                                  "text": "Utbildningsprogrammet för skolornas religionslärare"
                              },
                              {
                                  "langcode": "en",
                                  "text": "Degree Programme in Religion Teacher Education"
                              }
                          ]
                      },
                      {
                          "element_id": 30,
                          "code": "130016",
                          "start_date": "2013-07-31T21:00:00.000Z",
                          "end_date": "2017-01-15T22:00:00.000Z",
                          "name": [
                              {
                                  "langcode": "fi",
                                  "text": "Kahden opetettavan aineen (Uskonto ja psykologia) linja"
                              },
                              {
                                  "langcode": "sv",
                                  "text": "Linje för lärare i två undervisningsämnen (Religion och psykologi)"
                              },
                              {
                                  "langcode": "en",
                                  "text": "Teacher Education, Two School-Subjects Line (Religion and Psychology)"
                              }
                          ]
                      },
                      {
                          "element_id": 40,
                          "code": "03101",
                          "start_date": "2012-07-31T21:00:00.000Z",
                          "end_date": "2017-01-15T22:00:00.000Z",
                          "name": [
                              {
                                  "langcode": "fi",
                                  "text": "Kirkkohistoria"
                              },
                              {
                                  "langcode": "sv",
                                  "text": "Kyrkohistoria"
                              },
                              {
                                  "langcode": "en",
                                  "text": "Church History"
                              }
                          ]
                      }
                  ],
                  "admission_date": "2012-07-16T21:00:00.000Z",
                  "studyright_id": 90648547,
                  "end_date": "2017-01-15T22:00:00.000Z",
                  "duration_remaining_months": -45,
                  "priority": 30,
                  "start_date": "2012-07-31T21:00:00.000Z",
                  "extent": [
                      {
                          "langcode": "fi",
                          "text": "Alempi korkeakoulututkinto"
                      },
                      {
                          "langcode": "sv",
                          "text": "Lägre högskoleexamen"
                      },
                      {
                          "langcode": "en",
                          "text": "Bachelor\'s Degree"
                      }
                  ],
                  "organisation_name": [
                      {
                          "langcode": "fi",
                          "text": "Teologinen tiedekunta"
                      },
                      {
                          "langcode": "sv",
                          "text": "Teologiska fakulteten"
                      },
                      {
                          "langcode": "en",
                          "text": "Faculty of Theology"
                      }
                  ],
                  "cancel_date": null,
                  "organisation_code": "H10"
              },
              {
                  "degree_date": "2019-02-24T22:00:00.000Z",
                  "duration_months": 78,
                  "extent_code": 2,
                  "person_id": 90648546,
                  "study_start_date": "2017-01-16T22:00:00.000Z",
                  "state": [
                      {
                          "langcode": "fi",
                          "text": "Suorittanut tutkinnon"
                      },
                      {
                          "langcode": "sv",
                          "text": "Avlagt examen"
                      },
                      {
                          "langcode": "en",
                          "text": "Graduated"
                      }
                  ],
                  "faculty_code": "H10",
                  "elements": [
                      {
                          "element_id": 10,
                          "code": "00331",
                          "start_date": "2012-07-31T21:00:00.000Z",
                          "end_date": "2019-02-24T22:00:00.000Z",
                          "name": [
                              {
                                  "langcode": "fi",
                                  "text": "Teologian maisteri"
                              },
                              {
                                  "langcode": "sv",
                                  "text": "Teologie magister"
                              },
                              {
                                  "langcode": "en",
                                  "text": "Master of Theology"
                              }
                          ]
                      },
                      {
                          "element_id": 15,
                          "code": "A2004",
                          "start_date": "2012-07-31T21:00:00.000Z",
                          "end_date": "2019-02-24T22:00:00.000Z",
                          "name": [
                              {
                                  "langcode": "fi",
                                  "text": "Valtioneuvoston asetus (794/2004) yliopistojen tutkinnoista"
                              },
                              {
                                  "langcode": "sv",
                                  "text": "Statsrådets förordning (794/2004) om universitetsexamina"
                              },
                              {
                                  "langcode": "en",
                                  "text": "Government Decree (794/2004) on University Degrees"
                              }
                          ]
                      },
                      {
                          "element_id": 20,
                          "code": "120009",
                          "start_date": "2012-07-31T21:00:00.000Z",
                          "end_date": "2013-07-30T21:00:00.000Z",
                          "name": [
                              {
                                  "langcode": "fi",
                                  "text": "Kirkkojen ja yhteiskunnan teologisiin tehtäviin valmistava koulutusohjelma"
                              },
                              {
                                  "langcode": "sv",
                                  "text": "Utbildningsprogrammet för kyrkliga och samhälleliga teologiska uppgifter"
                              },
                              {
                                  "langcode": "en",
                                  "text": "Degree Programme in Theological Functions in Churches and Society"
                              }
                          ]
                      },
                      {
                          "element_id": 20,
                          "code": "120013",
                          "start_date": "2013-07-31T21:00:00.000Z",
                          "end_date": "2019-02-24T22:00:00.000Z",
                          "name": [
                              {
                                  "langcode": "fi",
                                  "text": "Koulun uskonnonopettajan tehtäviin valmistava koulutusohjelma"
                              },
                              {
                                  "langcode": "sv",
                                  "text": "Utbildningsprogrammet för skolornas religionslärare"
                              },
                              {
                                  "langcode": "en",
                                  "text": "Degree Programme in Religion Teacher Education"
                              }
                          ]
                      },
                      {
                          "element_id": 30,
                          "code": "130016",
                          "start_date": "2013-07-31T21:00:00.000Z",
                          "end_date": "2019-02-24T22:00:00.000Z",
                          "name": [
                              {
                                  "langcode": "fi",
                                  "text": "Kahden opetettavan aineen (Uskonto ja psykologia) linja"
                              },
                              {
                                  "langcode": "sv",
                                  "text": "Linje för lärare i två undervisningsämnen (Religion och psykologi)"
                              },
                              {
                                  "langcode": "en",
                                  "text": "Teacher Education, Two School-Subjects Line (Religion and Psychology)"
                              }
                          ]
                      },
                      {
                          "element_id": 40,
                          "code": "03110",
                          "start_date": "2012-07-31T21:00:00.000Z",
                          "end_date": "2019-02-24T22:00:00.000Z",
                          "name": [
                              {
                                  "langcode": "fi",
                                  "text": "Yleinen kirkkohistoria"
                              },
                              {
                                  "langcode": "sv",
                                  "text": "Allmän kyrkohistoria"
                              },
                              {
                                  "langcode": "en",
                                  "text": "General Church History"
                              }
                          ]
                      }
                  ],
                  "admission_date": "2012-07-16T21:00:00.000Z",
                  "studyright_id": 90648588,
                  "end_date": "2019-02-24T22:00:00.000Z",
                  "duration_remaining_months": -19,
                  "priority": 30,
                  "start_date": "2012-07-31T21:00:00.000Z",
                  "extent": [
                      {
                          "langcode": "fi",
                          "text": "Ylempi korkeakoulututkinto"
                      },
                      {
                          "langcode": "sv",
                          "text": "Högre högskoleexamen"
                      },
                      {
                          "langcode": "en",
                          "text": "Master\'s Degree"
                      }
                  ],
                  "organisation_name": [
                      {
                          "langcode": "fi",
                          "text": "Kirkkohistoria (Church history)"
                      },
                      {
                          "langcode": "sv",
                          "text": "Institutionen för kyrkohistoria"
                      },
                      {
                          "langcode": "en",
                          "text": "Department of Church History"
                      }
                  ],
                  "cancel_date": null,
                  "organisation_code": "H1002"
              },
              {
                  "degree_date": null,
                  "duration_months": 71,
                  "extent_code": 99,
                  "person_id": 90648546,
                  "study_start_date": "2013-07-31T21:00:00.000Z",
                  "state": [
                      {
                          "langcode": "fi",
                          "text": "Toissijainen"
                      },
                      {
                          "langcode": "sv",
                          "text": "Sekundär"
                      },
                      {
                          "langcode": "en",
                          "text": "Secondary"
                      }
                  ],
                  "faculty_code": null,
                  "elements": [
                      {
                          "element_id": 50,
                          "code": "477500",
                          "start_date": "2013-07-31T21:00:00.000Z",
                          "end_date": "2019-07-30T21:00:00.000Z",
                          "name": [
                              {
                                  "langcode": "fi",
                                  "text": "PSY100 Psykologian perusopinnot"
                              },
                              {
                                  "langcode": "sv",
                                  "text": "PSY100 Grundstudier i psykologi"
                              },
                              {
                                  "langcode": "en",
                                  "text": "PSY100 Basic Studies in Psychology"
                              }
                          ]
                      },
                      {
                          "element_id": 50,
                          "code": "477401",
                          "start_date": "2013-07-31T21:00:00.000Z",
                          "end_date": "2019-07-30T21:00:00.000Z",
                          "name": [
                              {
                                  "langcode": "fi",
                                  "text": "PSY200B Psykologian aineopinnot sivuaineopiskelijoille"
                              },
                              {
                                  "langcode": "sv",
                                  "text": "PSY200B Psykologi, ämnesstudier (biämne)"
                              },
                              {
                                  "langcode": "en",
                                  "text": "PSY200B Psychology, Intermediate Studies (Minor Subject)"
                              }
                          ]
                      }
                  ],
                  "admission_date": "2013-06-10T21:00:00.000Z",
                  "studyright_id": 96490415,
                  "end_date": "2019-07-30T21:00:00.000Z",
                  "duration_remaining_months": -14,
                  "priority": 2,
                  "start_date": "2013-07-31T21:00:00.000Z",
                  "extent": [
                      {
                          "langcode": "fi",
                          "text": "Muut erilliset opinnot"
                      },
                      {
                          "langcode": "sv",
                          "text": "Andra fristående studier"
                      },
                      {
                          "langcode": "en",
                          "text": "Other Non-Degree Studies"
                      }
                  ],
                  "organisation_name": [
                      {
                          "langcode": "fi",
                          "text": "Psykologia"
                      },
                      {
                          "langcode": "sv",
                          "text": "Psykologi"
                      },
                      {
                          "langcode": "en",
                          "text": "Psychology"
                      }
                  ],
                  "cancel_date": null,
                  "organisation_code": "6302"
              },
              {
                  "degree_date": null,
                  "duration_months": 56,
                  "extent_code": 10,
                  "person_id": 90648546,
                  "study_start_date": "2014-05-26T21:00:00.000Z",
                  "state": [
                      {
                          "langcode": "fi",
                          "text": "Toissijainen"
                      },
                      {
                          "langcode": "sv",
                          "text": "Sekundär"
                      },
                      {
                          "langcode": "en",
                          "text": "Secondary"
                      }
                  ],
                  "faculty_code": "H40",
                  "elements": [
                      {
                          "element_id": 0,
                          "code": "00901",
                          "start_date": "2014-05-26T21:00:00.000Z",
                          "end_date": "2019-02-24T22:00:00.000Z",
                          "name": [
                              {
                                  "langcode": "fi",
                                  "text": "Erilliset opinnot, arvosanat yms."
                              },
                              {
                                  "langcode": "sv",
                                  "text": "Fristående studier"
                              },
                              {
                                  "langcode": "en",
                                  "text": "Non-Degree Studies"
                              }
                          ]
                      },
                      {
                          "element_id": 40,
                          "code": "03402",
                          "start_date": "2014-05-26T21:00:00.000Z",
                          "end_date": "2019-02-24T22:00:00.000Z",
                          "name": [
                              {
                                  "langcode": "fi",
                                  "text": "Englantilainen filologia"
                              },
                              {
                                  "langcode": "sv",
                                  "text": "Engelsk filologi"
                              },
                              {
                                  "langcode": "en",
                                  "text": "English Philology"
                              }
                          ]
                      }
                  ],
                  "admission_date": "2014-05-26T21:00:00.000Z",
                  "studyright_id": 102286834,
                  "end_date": "2019-02-24T22:00:00.000Z",
                  "duration_remaining_months": -19,
                  "priority": 2,
                  "start_date": "2014-05-26T21:00:00.000Z",
                  "extent": [
                      {
                          "langcode": "fi",
                          "text": "Valinnaiset / Sivuaine"
                      },
                      {
                          "langcode": "sv",
                          "text": "Valfria / Biämne"
                      },
                      {
                          "langcode": "en",
                          "text": "Secondary Subject"
                      }
                  ],
                  "organisation_name": [
                      {
                          "langcode": "fi",
                          "text": "Englantilainen filologia"
                      },
                      {
                          "langcode": "sv",
                          "text": "Engelsk filologi"
                      },
                      {
                          "langcode": "en",
                          "text": "English Philology"
                      }
                  ],
                  "cancel_date": null,
                  "organisation_code": "4032"
              },
              {
                  "degree_date": null,
                  "duration_months": 52,
                  "extent_code": 2,
                  "person_id": 90648546,
                  "study_start_date": "2019-07-31T21:00:00.000Z",
                  "state": [
                      {
                          "langcode": "fi",
                          "text": "Ensisijainen"
                      },
                      {
                          "langcode": "sv",
                          "text": "Primär studierätt"
                      },
                      {
                          "langcode": "en",
                          "text": "Primary"
                      }
                  ],
                  "faculty_code": "H60",
                  "elements": [
                      {
                          "element_id": 10,
                          "code": "00337",
                          "start_date": "2019-07-31T21:00:00.000Z",
                          "end_date": "2023-12-30T22:00:00.000Z",
                          "name": [
                              {
                                  "langcode": "fi",
                                  "text": "Kasvatustieteen maisteri"
                              },
                              {
                                  "langcode": "sv",
                                  "text": "Pedagogie magister"
                              },
                              {
                                  "langcode": "en",
                                  "text": "Master of Arts (Education)"
                              }
                          ]
                      },
                      {
                          "element_id": 15,
                          "code": "A2004",
                          "start_date": "2019-07-31T21:00:00.000Z",
                          "end_date": "2023-12-30T22:00:00.000Z",
                          "name": [
                              {
                                  "langcode": "fi",
                                  "text": "Valtioneuvoston asetus (794/2004) yliopistojen tutkinnoista"
                              },
                              {
                                  "langcode": "sv",
                                  "text": "Statsrådets förordning (794/2004) om universitetsexamina"
                              },
                              {
                                  "langcode": "en",
                                  "text": "Government Decree (794/2004) on University Degrees"
                              }
                          ]
                      },
                      {
                          "element_id": 20,
                          "code": "MH60_001",
                          "start_date": "2019-07-31T21:00:00.000Z",
                          "end_date": "2023-12-30T22:00:00.000Z",
                          "name": [
                              {
                                  "langcode": "fi",
                                  "text": "Kasvatustieteiden maisteriohjelma"
                              },
                              {
                                  "langcode": "sv",
                                  "text": "Magisterprogrammet i pedagogik"
                              },
                              {
                                  "langcode": "en",
                                  "text": "Master´s Programme in Education"
                              }
                          ]
                      },
                      {
                          "element_id": 30,
                          "code": "SH60_042",
                          "start_date": "2019-07-31T21:00:00.000Z",
                          "end_date": "2023-12-30T22:00:00.000Z",
                          "name": [
                              {
                                  "langcode": "fi",
                                  "text": "Yleinen ja aikuiskasvatustiede"
                              },
                              {
                                  "langcode": "sv",
                                  "text": "Allmän- och vuxenpedagogik"
                              },
                              {
                                  "langcode": "en",
                                  "text": "General and Adult Education"
                              }
                          ]
                      }
                  ],
                  "admission_date": "2019-06-30T21:00:00.000Z",
                  "studyright_id": 130726863,
                  "end_date": "2023-12-30T22:00:00.000Z",
                  "duration_remaining_months": 39,
                  "priority": 1,
                  "start_date": "2019-07-31T21:00:00.000Z",
                  "extent": [
                      {
                          "langcode": "fi",
                          "text": "Ylempi korkeakoulututkinto"
                      },
                      {
                          "langcode": "sv",
                          "text": "Högre högskoleexamen"
                      },
                      {
                          "langcode": "en",
                          "text": "Master\'s Degree"
                      }
                  ],
                  "organisation_name": [
                      {
                          "langcode": "fi",
                          "text": "Kasvatustieteellinen tiedekunta"
                      },
                      {
                          "langcode": "sv",
                          "text": "Pedagogiska fakulteten"
                      },
                      {
                          "langcode": "en",
                          "text": "Faculty of Educational Sciences"
                      }
                  ],
                  "cancel_date": null,
                  "organisation_code": "H60"
              },
              {
                  "degree_date": null,
                  "duration_months": 9,
                  "extent_code": 9,
                  "person_id": 90648546,
                  "study_start_date": "2020-05-10T21:00:00.000Z",
                  "state": [
                      {
                          "langcode": "fi",
                          "text": "Toissijainen"
                      },
                      {
                          "langcode": "sv",
                          "text": "Sekundär"
                      },
                      {
                          "langcode": "en",
                          "text": "Secondary"
                      }
                  ],
                  "faculty_code": null,
                  "elements": [
                      {
                          "element_id": 0,
                          "code": "00997",
                          "start_date": "2020-05-10T21:00:00.000Z",
                          "end_date": "2021-02-23T22:00:00.000Z",
                          "name": [
                              {
                                  "langcode": "fi",
                                  "text": "Muu koulutus, joka ei johda tutkintoon"
                              },
                              {
                                  "langcode": "sv",
                                  "text": "Andra fristående studier"
                              },
                              {
                                  "langcode": "en",
                                  "text": "Other Non-Degree Studies"
                              }
                          ]
                      },
                      {
                          "element_id": 60,
                          "code": "AYEDUK304",
                          "start_date": "2020-05-10T21:00:00.000Z",
                          "end_date": "2021-02-23T22:00:00.000Z",
                          "name": [
                              {
                                  "langcode": "fi",
                                  "text": "Avoin yo: Oppiminen ja asiantuntijuus työssä, organisaatiossa ja verkostoissa"
                              },
                              {
                                  "langcode": "sv",
                                  "text": "Öppna uni: Lärande och expertis i arbetslivet, organisationer och nätverk"
                              },
                              {
                                  "langcode": "en",
                                  "text": "Open uni: Learning and expertise in the working life, organisations and networks"
                              }
                          ]
                      }
                  ],
                  "admission_date": "2020-05-10T21:00:00.000Z",
                  "studyright_id": 136361664,
                  "end_date": "2021-02-23T22:00:00.000Z",
                  "duration_remaining_months": 5,
                  "priority": 2,
                  "start_date": "2020-05-10T21:00:00.000Z",
                  "extent": [
                      {
                          "langcode": "fi",
                          "text": "Avoin yliopisto"
                      },
                      {
                          "langcode": "sv",
                          "text": "Öppet universitet"
                      },
                      {
                          "langcode": "en",
                          "text": "Open University"
                      }
                  ],
                  "organisation_name": [
                      {
                          "langcode": "fi",
                          "text": "Avoin yliopisto"
                      },
                      {
                          "langcode": "sv",
                          "text": "Öppna universitetet"
                      },
                      {
                          "langcode": "en",
                          "text": "Open University"
                      }
                  ],
                  "cancel_date": null,
                  "organisation_code": "9301"
              }
          ]
      }';
    }

    // Enforce arrays everywhere.
    $mock_json = (array) json_decode($mock_json, TRUE);
    $mock_json['data'] = (array) $mock_json['data'];
    foreach($mock_json['data'] as $key => $value){
      $mock_json['data'][$key] = (array) $value;
    }

    if (Settings::get('uhsg_oprek_add_debug_logging', self::UHSG_OPREK_ADD_DEBUG_LOGGING)) {
      \Drupal::logger('uhsg_oprek')->info('GetStudyRights was executed with mock response of type <i>@complexity </i>.', [
        '@complexity' => $complexity,
      ]);
    }

    return $mock_json['data'];
  }

}
