<?php

namespace Drupal\uhsg_sisu\Services;

interface StudyRightsServiceInterface {

  /**
   * Fetch studyrights for person.
   *
   * @param string $oodiId
   *   Student Number.
   *
   * @return array|null
   *   raw data or NULL.
   */
  public function fetchStudyRightsData($oodiId);

  /**
   * get all active studyrights for person.
   *
   * @param string $oodiId
   *   Student Number.
   *
   * @return array|null
   *   array of studyright obj or NULL.
   */
  public function getActiveStudyRights($oodiId);

  /**
   * Get Student Primary Degree Program. This will follow the PrimalityChain
   * and find the Primary Degree Program based on the data in there.
   *
   * @param int $oodiId
   *   User Oodi ID.
   *
   * @return \Psr\Http\Message\ResponseInterface
   *   Response object.
   */
  public function getPrimaryStudentDegreeProgram($oodiId);

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
  public function getActiveStudentDegreeProgram($studyright);
}
