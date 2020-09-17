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
          throw new \Exception('Oprek service responded, but body status is not OK', ($response->getStatusCode() * 1000) + $this->getStatusFromBody($body));
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
    // This mock json is a real life example of a person (person_id anonymized)
    // which earlier versions had trouble with. For example recognizing
    // what is the primary study right/subject failed in rare cases.
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

    // Ensure arrays everywhere.
    $mock_json = (array) json_decode($mock_json);
    $mock_json['data'] = (array) $mock_json['data'];
    foreach($mock_json['data'] as $key => $value){
      $mock_json['data'][$key] = (array) $value;
    }

    if (Settings::get('uhsg_oprek_add_debug_logging', self::UHSG_OPREK_ADD_DEBUG_LOGGING)) {
      \Drupal::logger('uhsg_oprek')->info('GetStudyRights was executed with mock response: <pre> @mock </pre>', [
        '@mock' => print_r($mock_json['data'], TRUE),
      ]);
    }

    return $mock_json['data'];
  }

}
