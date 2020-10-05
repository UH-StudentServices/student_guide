<?php

namespace Drupal\uhsg_sisu\Services;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Logger\RfcLogLevel;
use Drupal\Core\Utility\Error;
use Drupal\uhsg_sisu\Services\SisuService;
use Drupal\uhsg_sisu\StudyRight\StudyRight\StudyRight;

use GuzzleHttp\Exception\GuzzleException;

/**
 * Class StudyRightsService.
 *
 * @package Drupal\uhsg_sisu\Services\StudyRightsService
 */
class StudyRightsService {

  // Use mock responses
  const UHSG_SISU_MOCK_RESPONSE = FALSE;

  /**
   * SisuService.
   *
   * @var \Drupal\uhsg_sisu\Services\SisuService
   */
  private $sisuService;

  /**
   * Drupal settings.
   *
   * @var \Drupal\Core\Site\Settings
   */
  private $settings;

  /**
   * Drupal\Core\Logger\LoggerChannelInterface definition.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  private $logger;

  /**
   * Drupal\Component\Serialization\SerializationInterface definition.
   *
   * @var \Drupal\Component\Serialization\SerializationInterface
   */
  private $jsonSerialization;

  /**
   * Drupal\Core\Cache\CacheBackendInterface definition.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  private $cache;

  /**
   * Drupal\Component\Datetime\TimeInterface definition.
   *
   * @var \Drupal\Component\Datetime\TimeInterface
   */
  private $time;

  /**
   * Static storage for study rights data.
   *
   * @var array
   */
  private $studyRightsData;

  /**
   * Service constructor.
   *
   * @param \Drupal\Core\Site\Settings $settings
   *   The Drupal settings.
   * @param Drupal\uhsg_sisu\Services\SisuService $sisuService
   *   SisuService.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $loggerChannelFactory
   *   The logger factory.
   * @param \Drupal\Component\Serialization\SerializationInterface $jsonSerialization
   *   The JSON serializer.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache
   *   The cache backend.
   * @param \Drupal\Component\Datetime\TimeInterface $time
   *   The Drupal time object.
   */
  public function __construct(Settings $settings, 
                              SisuService $sisuService, 
                              LoggerChannelFactoryInterface $loggerFactory, 
                              SerializationInterface $jsonSerialization, 
                              CacheBackendInterface $cache, 
                              TimeInterface $time) {
    $this->settings = $settings;
    $this->sisuService = $sisuService;
    $this->logger = $loggerChannelFactory->get('uhsg_sisu');
    $this->jsonSerialization = $jsonSerialization;
    $this->cache = $cache;
    $this->time = $time;    
  }

  /**
   * Fetch studyrights for person.
   *
   * @param string $oodiId
   *   Student Number.
   *
   * @return array|null
   *   raw data or NULL.
   */
  public function fetchStudyRightsData($oodiId) {
    // Fetch from mockdata based on configuration
    if ($this->settings::get('uhsg_sisu_mock_response', self::UHSG_SISU_MOCK_RESPONSE)) {
      return fetchStudyRightsMockData();
    }

    // Fetch from static storage if it has data.
    if (is_array($this->studyRightsData) && array_key_exists($oodiId, $this->studyRightsData)) {
      return $this->studyRightsData[$oodiId];
    }

    $query = [
      "operationName" => "getStudyRights",
      "variables" => [
        "ids" => [
          "hy-hlo-" . $oodiId,
        ],
      ],
      "query" => 'query StudyRightsQuery($personId: ID!) {
        private_person(id: $personId) {
          studyRightPrimalityChain {
            studyRightPrimalities {
              studyRightId
              startDate
              endDate
              documentState
            }
          }
          studyRights {
            id
            valid {
              startDate
              endDate
            }
            studyRightGraduation {
              phase1GraduationDate
              phase2GraduationDate
            }
            acceptedSelectionPath {
              educationPhase1Child {
                code
                groupId
                name {fi sv en}
              }
              educationPhase1 {
                code
                groupId
                name {
                  fi
                  sv
                  en
                }
              }
              educationPhase2Child {
                code
                groupId
                name {fi sv en}
              }
              educationPhase2 {
                code
                groupId
                name {
                  fi
                  sv
                  en
                }
              }
            }
          }
        }
      }',
    ];

    try {
      $data = $this->sisuService->apiRequest($query);

      // Save to static storage.
      $this->studyRightsData[$oodiId] = $data;
      return $this->studyRightsData[$oodiId];

    }
    catch (GuzzleException $e) {
      $this->guzzleErrorLog($e);
    }
    catch (\Exception $e) {
      $variables = Error::decodeException($e);
      $this->log('%type: @message in %function (line %line of %file) @backtrace_string.', $variables, RfcLogLevel::ERROR);
    }

    $this->log('StudyRightsService encountered an unknown error when fetching StudyRights with query@studyrights', ['@studyrights' => $query], RfcLogLevel::ERROR);
    return FALSE;
  }

  /**
   * get all active studyrights for person.
   *
   * @param string $oodiId
   *   Student Number.
   *
   * @return array|null
   *   array of studyright obj or NULL.
   */
  public function getActiveStudyRights($oodiId) {
    // Fetch studyrightsdata for student.
    $data = Json::decode($this->fetchStudyRightsData($oodiId));
    $date_today = date('Y-m-d', time());

    // Make sure we have results to loop trough.
    if(!$data || $data['data'] || $data['data']['private_person'] || $data['data']['private_person']['studyRights']) {
      return null;
    }

    // Save all studyrights.
    $studyrightprimalitychain = $data['data']['private_person']['studyRightPrimalityChain'];
    $studyrights = $data['data']['private_person']['studyRights'];
    $primarystudyright = getPrimaryStudentDegreeProgram($oodiId);

    $active_studyrights = [];
    // Loop trough studyrights and save active studyrights.
    foreach ($studyrights as $studyright) {
      // Only save studyright if it's active ie. enddate null and startdate in the past
      if($studyright['valid']['startDate'] < $date_today && !$studyright['valid']['endDate']) {
        // Handle specialization and graduation for a studyright
        $studyrightdegreeprogram = getActiveStudentDegreeProgram($studyright);

        // Create new studyright
        $studyrightdegree = new StudyRight($studyrightdegreeprogram);

          // If primary, then set it so.
        if($primarystudyright['id'] == $studyright['id']) {
          $studyrightdegreeprogram.setPrimary(TRUE);
        }

        $active_studyrights[] = $studyrightdegreeprogram;
      }
    }

    return $active_studyrights;
  }

  /**
   * Get Student Primary Degree Program.
   *
   * @param int $oodiId
   *   User Oodi ID.
   *
   * @return \Psr\Http\Message\ResponseInterface
   *   Response object.
   */
  public function getPrimaryStudentDegreeProgram($oodiId) {
    // Fetch studyrightsdata for student
    $data = Json::decode($this->fetchStudyRightsData($oodiId));

    if(!$data || $data['data']['private_person']) {
      return null;
    }

    $studyrightprimalitychain = $data['data']['private_person']['studyRightPrimalityChain'];
    $studyrights = $data['data']['private_person']['studyRights'];

    // Get primarystudyright from data.
    $primarystudyright = getPrimaryStudyRight($studyrightprimalitychain, $studyrights);

    // We have no primarystudyrights
    if(!$primarystudyright) {
      return null;
    }

    // Handle specialisation properly
    return getActiveStudentDegreeProgram($primarystudyright);
  }

  /**
   * Get Active DegreeProgram from studyright.
   *
   * @param array $studyright
   *   Studyright array.
   *
   * @return array degreeprogram
   *   Response object.
   */
  public function getActiveStudentDegreeProgram($studyright) {
    // Check if we have graduated from phase1 studies
    if($studyright['studyRightGraduation'] && $studyright['studyRightGraduation']['phase1GraduationDate'] && $studyright['acceptedSelectionPath']['educationPhase2']) {
      // We have graduated from phase1, move to phase2
      $phase1graduated = TRUE;
    }

    // if we have graduated then degree program is phase2
    $degreeprogram = $phase1graduated ? $studyright['acceptedSelectionPath']['educationPhase2'] : $studyright['acceptedSelectionPath']['educationPhase1'];
    $degreeprogramchild = $phase1graduated ? $studyright['acceptedSelectionPath']['educationPhase2Child'] : $studyright['acceptedSelectionPath']['educationPhase1Child'];

    // Handle specialisation properly
    return degreeProgramWithSpecialisation($degreeprogram, $degreeprogramchild);
  }

  /**
   * Get Primary Study Right
   */
  private function getPrimaryStudyRight($studyRightPrimalityChain, $studyRights) {
    // Make sure we have all the needed data.
    if (!$studyRightPrimalityChain || !$studyRightPrimalityChain['studyRightPrimalities']
      || !strlen($studyRightPrimalityChain['studyRightPrimalities']) || !$studyRights || !strlen($studyRights)) {
      return null;
    }

    // Make sure all data looks ok and return proper id if we have active studyright
    $studyrightprimalities = $studyRightPrimalityChain['studyRightPrimalities'];

    // Loop trough all studyrightprimalities and find "last" active primality
    foreach($studyrightprimalities as $id => $studyrightprimality) {
      if($studyrightprimality['startDate'] && !$studyrightprimality['endDate'] && $studyrightprimality['documentState'] == 'ACTIVE') {
        $studyRightId = $studyrightprimality['studyRightId'];
      }
    }

    // Loop trough studyrights and return the correct one
    foreach($studyrights as $id => $studyright) {
      if($studyright['id'] == $studyRightId) {
        return $studyright;
      }
    }
  }

  /*
  https://jira.it.helsinki.fi/browse/OPISKELU-506
  Students of the Faculty of Educational Sciences get special treatment.
  1) Their specialisation name is added to the name of their degree programme.
  2) The Guide News are fetched for them using a combination of the degree programme and specialisation codes.
  An additional twist: the specialisation codes are different in Sisu and Oodi. Guide news API currently only supports Oodi specialisation codes.
  Since we get the Sisu specialisation module groupId from the study rights, and we have to fetch news using the Oodi code,
  we need to have a mapping from Sisu module groupId to Oodi specialisation codes.
  Note: the Sisu module ids in question are the same in QA and production.
  */
  private function degreeProgramWithSpecialisation($degreeProgram, $specialisation) {
    if ($degreeProgram && $specialisation && $specialisation['groupId']) {
      // Read file
      $sisu_oodi_codes = Json::decode(file_get_contents("./sisu-oodi-codes.json"));

      // Traverse trough all the oodi-sisu mappings.
      foreach($sisu_oodi_codes as $group) {
        // If we encounter a mapping that fits, then mark that
        if($group['groupId'] == $specialisation['groupId']) {
          $oodiMapping = $group;
        }
      }

      // If we have a match, then we need to modify our degreeprogram before returning it.
      if ($oodiMapping) {
        $degreeProgram['code'] .= $oodiMapping['oodiSpecialisationCode'];
        $degreeProgram['name'] .= $specialisation['name'];
      }
    }

    // Return either modified or unmodified degreeprogram
    return $degreeProgram;
  }

  /**
   * GetstudyRightsMockdata.
   */
  private function fetchStudyRightsMockData() {
    // Read file and return mocked data.
    return file_get_contents("../../example_data/private_person_study_rights.json");
  }

  /**
   * Logger.
   *
   * @see LoggerInterface::log()
   */
  private function log($message, $context = [], $severity = RfcLogLevel::NOTICE) {
    $this->loggerFactory->get('uhsg_sisu')->log($severity, $message, $context);
  }

  /**
   * Guzzle exception logger.
   *
   * @param GuzzleHttp\Exception\GuzzleException $error
   *   Guzzle exception.
   */
  private function guzzleErrorLog(GuzzleException $error) {
    $response_info = '';

    // Get the original response.
    if ($response = $error->getResponse()) {
      // Get the info returned from the remote server.
      $response_info = $response->getBody()->getContents();
    }

    // Log the error.
    $this->log('API connection error. Error details are as follows:<pre>@response</pre>', [
      '@response' => print_r(json_decode($response_info), TRUE),
    ], RfcLogLevel::ERROR);
  }
}