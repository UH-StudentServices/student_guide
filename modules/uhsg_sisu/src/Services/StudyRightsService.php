<?php

namespace Drupal\uhsg_sisu\Services;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Logger\RfcLogLevel;
use Drupal\Core\Utility\Error;
use Drupal\Core\Site\Settings;
use Drupal\Core\Config\ConfigFactory;
use Drupal\uhsg_sisu\Services\SisuService;
use Drupal\uhsg_sisu\Services\StudyRight\StudyRight;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;

/**
 * Class StudyRightsService.
 *
 * @package Drupal\uhsg_sisu\Services\StudyRightsService
 */
class StudyRightsService implements StudyRightsServiceInterface {

  /*
  * This can be overridden in settings.local.php with:
  *   $settings['uhsg_sisu_mock_response'] = TRUE;
  */
  const UHSG_SISU_MOCK_RESPONSE = FALSE;

  /*
  * There are a mock responses for a few users available in the example_data
  * folder. When testing different users, one can change this eg. to:
  *  private_person_study_rights_doo_6.json (doo_7, doo_20, doo_81, doo_83..).
  */
  const UHSG_SISU_MOCK_FILE = 'private_person_study_rights.json';

  /*
  * Logging responses is helpful for debugging.
  * This can be overridden in settings.local.php with:
  *   $settings['uhsg_sisu_log_responses'] = TRUE;
  */
  const UHSG_SISU_LOG_RESPONSES = FALSE;

  /**
   * SisuService.
   *
   * @var \Drupal\uhsg_sisu\Services\SisuService
   */
  private $sisuService;

  /**
   * Logger Factory.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  private $loggerFactory;

  /**
   * Static storage for study rights data.
   *
   * @var array
   */
  private $studyRightsData;

  /**
   * Config.
   *
   * @var \Drupal\Core\Config\ConfigFactory
   */
  private $config;

  /**
   * Service constructor.
   *
   * @param Drupal\uhsg_sisu\Services\SisuService $sisuService
   *   SisuService.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $loggerFactory
   *   LoggerChannelFactory.
   * @param \Drupal\Core\Config\ConfigFactory $config
   *   Config.
   */
  public function __construct(SisuService $sisuService,
                              LoggerChannelFactoryInterface $loggerFactory,
                              ConfigFactory $config) {
    $this->sisuService = $sisuService;
    $this->loggerFactory = $loggerFactory;
    $this->config = $config->get('uhsg_sisu.settings');
  }

  /**
   * Fetch studyrights for person.
   *
   * @param string $hyPersonId
   *   Sisu ID.
   *
   * @return array|null
   *   raw data or NULL.
   */
  public function fetchStudyRightsData($hyPersonId) {
    // Fetch from mockdata based on configuration
    if ($this->config->get('uhsg_sisu_mock_response', self::UHSG_SISU_MOCK_RESPONSE)) {
      return $this->fetchStudyRightsMockData();
    }

    // Fetch from static storage if it has data.
    if (is_array($this->studyRightsData) && array_key_exists($hyPersonId, $this->studyRightsData)) {
      return $this->studyRightsData[$hyPersonId];
    }

    // StudyRights Query
    $query = [
      "operationName" => "fetchStudyRights",
      "variables" => [
        "id" => $hyPersonId,
      ],
      "query" => 'query fetchStudyRights($id: ID!) {
        private_person(id: $id) {
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
                name {
                  fi
                  sv
                  en
                }
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
                name {
                  fi
                  sv
                  en
                }
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
      $this->studyRightsData[$hyPersonId] = $data;
      return $this->studyRightsData[$hyPersonId];

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
   * @param string $hyPersonId
   *   Sisu ID.
   *
   * @return array|null
   *   array of studyright obj or NULL.
   */
  public function getActiveStudyRights($hyPersonId) {
    // Initialize variables.
    $data = NULL;
    $sisuResponse = NULL;
    $date_today = date('Y-m-d', time());

    // Fetch studyrightsdata for student.
    if ($hyPersonId) {
      $sisuResponse = (array) $this->fetchStudyRightsData($hyPersonId);
    }

    // Log full response for convenient debugging (enabled on local/qa).
    if (Settings::get('uhsg_sisu_log_responses', self::UHSG_SISU_LOG_RESPONSES)){
      $this->log('getActiveStudyRights() sisuResponse:
         <pre>@sisuResponse</pre>', [
          '@sisuResponse' => print_r($sisuResponse, TRUE),
      ], RfcLogLevel::INFO);
    }

    // Proper Response Handling. Note: this is not a Guzzle object!
    if (!empty($sisuResponse['data']['private_person']['studyRights'])) {
      $data = $sisuResponse;
    } else {
      // Make sure we have results to loop trough.
      return null;
    }

    // Save all studyrights.
    $studyrightprimalitychain = $data['data']['private_person']['studyRightPrimalityChain'];
    $studyrights = $data['data']['private_person']['studyRights'];

    // Get primarystudyright from data.
    $primarystudyright = $this->getPrimaryStudentDegreeProgram($hyPersonId);

    $active_studyrights = [];
    // Loop trough studyrights and save active studyrights.
    foreach ($studyrights as $studyright) {
      // Only save studyright if it's active ie.startdate in the past and
      // enddate either null (not set) or after current date.
      if($studyright['valid']['startDate'] < $date_today &&
        (empty($studyright['valid']['endDate']) || $studyright['valid']['endDate'] > $date_today)) {
        // Handle specialization and graduation for a studyright
        $studyrightdegreeprogram = $this->getActiveStudentDegreeProgram($studyright);

        // Create new studyright
        $studyrightdegree = new StudyRight($studyrightdegreeprogram);

        // If primary, then set it so.
       if($primarystudyright['id'] == $studyright['id']) {
         $studyrightdegree->setPrimary(TRUE);
       }

        $active_studyrights[] = $studyrightdegree;
      }
    }

    // Log studyrights? Enabled on local/qa.
    if (Settings::get('uhsg_sisu_log_responses', self::UHSG_SISU_LOG_RESPONSES)){
      $responseAsArray = (array) $sisuResponse;
      $this->log('getActiveStudyRights()
        studyrights:
        <pre>@studyrights</pre>

        active_studyrights:
          <pre>@active_studyrights</pre>

        last_studyrightdegree:
        <pre>@last_studyrightdegree</pre>', [
          '@studyrights' => print_r($studyrights, TRUE),
          '@active_studyrights' => print_r($active_studyrights, TRUE),
          '@last_studyrightdegree' => print_r($studyrightdegree, TRUE),
      ], RfcLogLevel::INFO);
    }

    return $active_studyrights;
  }

  /**
   * Get Student Primary Degree Program. This will follow the PrimalityChain
   * and find the Primary Degree Program based on the data in there.
   *
   * @param int $hyPersonId
   *   Sisu ID.
   *
   * @return \Psr\Http\Message\ResponseInterface
   *   Response object.
   */
  public function getPrimaryStudentDegreeProgram($hyPersonId) {
    // Initialize variables.
    $data = NULL;
    $sisuResponse = NULL;

    // Fetch studyrightsdata for student.
    if ($hyPersonId) {
      $sisuResponse = (array) $this->fetchStudyRightsData($hyPersonId);
    }

    // Proper Response Handling.
    if (!empty($sisuResponse)) {
      $data = $sisuResponse;
    } else {
      // Make sure we have results to loop trough.
      return null;
    }

    $studyrightprimalitychain = $data['data']['private_person']['studyRightPrimalityChain'];
    $studyrights = $data['data']['private_person']['studyRights'];

    // Get primarystudyright from data.
    $primarystudyright = $this->getPrimaryStudyRight($studyrightprimalitychain, $studyrights);

    // We have no primarystudyrights
    if(!$primarystudyright) {
      return null;
    }

    // Handle specialisation properly
    return $this->getActiveStudentDegreeProgram($primarystudyright);
  }

  /**
   * Get Active DegreeProgram from studyright. This will return Active
   * DegreeProgram based on graduation data inside StudyRights.
   *
   * @param array $studyright
   *   Studyright array.
   *
   * @return array degreeprogram
   *   Response object.
   */
  public function getActiveStudentDegreeProgram($studyright) {
    // Check if we have graduated from phase1 studies
    $phase1graduated = FALSE;
    if(!empty($studyright['studyRightGraduation']) && !empty($studyright['studyRightGraduation']['phase1GraduationDate']) && !empty($studyright['acceptedSelectionPath']['educationPhase2'])) {
      // We have graduated from phase1, move to phase2
      $phase1graduated = TRUE;
    }

    // if we have graduated then degree program is phase2
    $degreeprogram = $phase1graduated ? $studyright['acceptedSelectionPath']['educationPhase2'] : $studyright['acceptedSelectionPath']['educationPhase1'];
    $degreeprogramchild = $phase1graduated ? $studyright['acceptedSelectionPath']['educationPhase2Child'] : $studyright['acceptedSelectionPath']['educationPhase1Child'];
    // $degreeprogramchild is NULL in many cases, its not required.

    // Studyright should be added to degree Program ID for later checks to work!
    $degreeprogram['id'] = $studyright['id'];

    // Handle specialisation properly
    return $this->degreeProgramWithSpecialisation($degreeprogram, $degreeprogramchild);
  }

  /**
   * Get Primary Study Right.
   * This will return primary studyright from primalitychain and studyright data.
   */
  private function getPrimaryStudyRight($studyRightPrimalityChain, $studyRights) {
    $studyRightId = '';
    // Make sure we have all the needed data.
    if (empty($studyRightPrimalityChain['studyRightPrimalities']) || empty($studyRights)) {
      return null;
    }

    // Make sure all data looks ok and return proper id if we have active studyright
    // Loop trough all studyrightprimalities and find "last" active primality
    foreach($studyRightPrimalityChain['studyRightPrimalities'] as $id => $studyrightprimality) {
      if($studyrightprimality['startDate'] && empty($studyrightprimality['endDate']) && $studyrightprimality['documentState'] == 'ACTIVE') {
        $studyRightId = $studyrightprimality['studyRightId'];
      }
    }

    // Loop trough studyrights and return the correct one
    foreach($studyRights as $id => $studyright) {
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
    $oodiMapping = array();
    $langcode = \Drupal::languageManager()->getCurrentLanguage()->getId();;
    if (!empty($degreeProgram) && !empty($specialisation['groupId'])) {
      // Read file
      $path = getcwd() . "/". drupal_get_path('module', 'uhsg_sisu') . "/src/Services/sisu-oodi-codes.json";
      $sisu_oodi_codes = Json::decode(file_get_contents($path));

      // Traverse trough all the oodi-sisu mappings.
      foreach($sisu_oodi_codes as $group) {
        // If we encounter a mapping that fits, then mark that
        if($group['groupId'] == $specialisation['groupId']) {
          $oodiMapping = $group;
        }
      }

      // If we have a match, then we need to modify our degreeprogram before returning it.
      if (!empty($oodiMapping['oodiSpecialisationCode']) && !empty($specialisation['name'])) {
        $degreeProgram['code'] .= $oodiMapping['oodiSpecialisationCode'];
        if(!empty($degreeProgram[$langcode]['name']) && !empty($specialisation[$langcode]['name'])){
          $degreeProgram[$langcode]['name'] .= $specialisation[$langcode]['name'];
        }
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
    $path = getcwd() . "/". drupal_get_path('module', 'uhsg_sisu') . "/example_data/" . $self::UHSG_SISU_MOCK_FILE;
    return file_get_contents($path);
  }

  /**
   * Logger.
   *
   * @see LoggerInterface::log()
   */
  private function log($message, $context = [], $severity = RfcLogLevel::NOTICE) {
    $this->loggerFactory->get('uhsg_sisu')->log($severity, $message, $context);
  }
}
