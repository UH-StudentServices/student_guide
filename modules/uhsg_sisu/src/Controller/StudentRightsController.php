<?php

namespace Drupal\uhsg_sisu\Controller;

use Drupal\uhsg_sisu\Service\SisuService;
use GuzzleHttp\Exception\GuzzleException;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Logger\RfcLogLevel;

/**
 * Class StudentRightsController.
 *
 * @package Drupal\uhsg_sisu\Controller
 */
class StudentRightsController {

  /**
   * SisuService.
   *
   * @var \Drupal\uhsg_sisu\Service\SisuService
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
   * @param Drupal\uhsg_sisu\Service\SisuService $sisuService
   *   SisuService.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $loggerFactory
   *   LoggerChannelFactory.
   */
  public function __construct(SisuService $sisuService, LoggerChannelFactoryInterface $loggerFactory) {
    // Moodi Service.
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
    // this has been duplicated from:
    // https://version.helsinki.fi/OPADev/studies/-/blob/master/backend/src/integrations/sisu/query/study-rights-query.js
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
   * Get Primary Study Right.
   *
   * @param int $oodiId
   *   User Oodi ID.
   *
   * @return \Psr\Http\Message\ResponseInterface
   *   Response object.
   */
  public function getStudentDegreeProgram($oodiId) {
    // Need to duplicate this code in php:
    // https://version.helsinki.fi/OPADev/studies/-/blob/master/backend/src/services/users.js#L56
    try {
      return $this->getStudyRights($oodiId);
    }
    catch (GuzzleException $e) {
      // Do nothing.
    }

    $this->log('StudentRightsController encountered an unknown error when retrieving StudyRights for @oodiId', ['@oodiId' => $oodiId], RfcLogLevel::ERROR);
    return FALSE;
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
