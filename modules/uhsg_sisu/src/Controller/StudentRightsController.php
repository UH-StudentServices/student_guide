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
      return $this->sisuService->getStudyRights($oodiId);
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
