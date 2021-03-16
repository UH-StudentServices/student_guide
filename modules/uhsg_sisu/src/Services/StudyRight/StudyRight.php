<?php

namespace Drupal\uhsg_sisu\Services\StudyRight;

/**
 * Represents StudyRight object.
 */
class StudyRight {

  /**
   * True if this is a primary studyright for the student.
   */
  public $primary;

  /**
   * Save the code for studyright.
   */
  public $code;

  /**
   * StudyRight constructor.
   * @param array $degreeprogram
   *   degreeprogram under studyright
   */
  public function __construct($degreeprogram) {
      $this->code = $degreeprogram['code'];
  }

  /**
   * Set this studyright as primary studyright.
   *
   * @param $primary
   */
  public function setPrimary($primary) {
    $this->primary = $primary;
  }

  /**
   * Set this studyright as primary studyright.
   *
   * @param $primary
   */
  public function isPrimary() {
    return $this->primary;
  }

  /**
   * {@inheritdoc}
   */
  public function getCode() {
    // If "erikoislääkärit".
    if($this->getSpecialCode($this->code)) {
      return $this->getSpecialCode($this->code);
    }
    // Else.
    else {
      return $this->code;
    }
  }

  /**
   * Handle special case for Special Sisu Codes.
   *
   * @param $code
   */
  protected function getSpecialCode($code) {
    $specialCodes = [
      '320018', // Special Doctors 5 year education.
      '320019', // Special Doctors 6 year education.
      '320006', // Special dentistry education.
      '220102' // Professional Licenciates.
    ];

    if(in_array(substr($code, 0, 6), $specialCodes)) {
      return substr($code, 0, 6);
    } else {
      return NULL;
    }
  }
}
