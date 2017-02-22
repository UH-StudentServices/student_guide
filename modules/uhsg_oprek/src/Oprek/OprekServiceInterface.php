<?php

namespace Drupal\uhsg_oprek\Oprek;

interface OprekServiceInterface {

  /**
   * Returns an version of the backend service.
   * @return string
   */
  public function getVersion();

  /**
   * Gets study rights of given student.
   * @param $studentNumber
   * @return array
   */
  public function getStudyRights($studentNumber);

}
