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
    return $this->code;
  }
}
