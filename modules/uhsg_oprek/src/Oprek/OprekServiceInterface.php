<?php

namespace Drupal\uhsg_oprek\Oprek;

use Drupal\uhsg_oprek\Oprek\StudyRight\StudyRightInterface;

interface OprekServiceInterface {

  /**
   * Returns an version of the backend service.
   * @return string
   */
  public function getVersion();

  /**
   * Gets study rights of given student.
   * @param $studentNumber
   * @return StudyRightInterface[]
   */
  public function getStudyRights($studentNumber);

}
