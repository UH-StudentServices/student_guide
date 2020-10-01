<?php

namespace Drupal\uhsg_sisu\Services\StudentRightsService;

use Drupal\uhsg_sisu\Services\SisuService;
use GuzzleHttp\Exception\GuzzleException;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Logger\RfcLogLevel;

/**
 * Class StudentRightsService.
 *
 * @package Drupal\uhsg_sisu\Services\StudentRightsService
 */
class StudentRightsService {

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
  protected $loggerFactory;

  /**
   * Controller constructor.
   *
   * @param Drupal\uhsg_sisu\Services\SisuService $sisuService
   *   SisuService.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $loggerFactory
   *   LoggerChannelFactory.
   */
  public function __construct(SisuService $sisuService, LoggerChannelFactoryInterface $loggerFactory) {
    // Sisu Service.
    $this->sisuService = $sisuService;
    // Logger factory.
    $this->loggerFactory = $loggerFactory;
  }

  /**
   * Fetch studyrights for person.
   *
   * @param string $oodiUid
   *   Oodi user id.
   *
   * @return array|null
   *   JSON decoded data or NULL.
   */
  public function getStudyRights($oodiUid) {
    $query = [
      "operationName" => "getStudyRights",
      "variables" => [
        "ids" => [
          "hy-hlo-" . $oodiUid,
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

    return $this->sisuService->apiRequest($query);
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
    try {
      // Fetch StudyRights from Sisu.
      $data = $this->getStudyRights($oodiId);

      if(!$data || $data$data['data']['private_person']) {
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

      if($primarystudyright['studyRightGraduation'] && $primarystudyright['studyRightGraduation']['phase1GraduationDate'] && $primarystudyright['acceptedSelectionPath']['educationPhase2']) {
        // We have graduated from phase1, move to phase2
        $phase1graduated = TRUE;
      }

      // if we have graduated then degree program is phase2
      $degreeprogram = $phase1graduated ? $primarystudyright['acceptedSelectionPath']['educationPhase2'] : $primarystudyright['acceptedSelectionPath']['educationPhase1'];
      $degreeprogramchild = $phase1graduated ? $primarystudyright['acceptedSelectionPath']['educationPhase2Child'] : $primarystudyright['acceptedSelectionPath']['educationPhase1Child'];

      // Handle specialisation properly
      return degreeProgramWithSpecialisation($degreeprogram, $degreeprogramchild);
    }
    catch (GuzzleException $e) {
      // Do nothing.
    }

    $this->log('StudentRightsController encountered an unknown error when retrieving StudyRights for @oodiId', ['@oodiId' => $oodiId], RfcLogLevel::ERROR);
    return FALSE;
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
      $sisu_oodi_codes = json_decode(file_get_contents("./sisu-oodi-codes.json"));

      // Traverse trough all the oodi-sisu mappings.
      foreach($sisu_oodi_codes as $group) {
        // If we encounter a mapping that fits, then mark that
        if($group['groupId'] == $specialisation['groupId']) {
          $oodiMapping = $group;
        }
      }

      // If we have a match, then we need to modify our degreeprogram before returning it.
      if ($oodiMapping) {
        $degreeProgram['code'] = $degreeProgram['code'] . $oodiMapping['oodiSpecialisationCode'];
        $degreeProgram['name'] = $degreeProgram['name'] . $specialisation['name'];
      }
    }

    // Return either modified or unmodified degreeprogram
    return $degreeProgram;
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
