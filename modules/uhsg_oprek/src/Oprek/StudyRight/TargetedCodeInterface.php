<?php

namespace Drupal\uhsg_oprek\Oprek\StudyRight;

/**
 * TargetedCode represents a logical code of StudyRight that is assembled using
 * StudyRight elements.
 */
interface TargetedCodeInterface {

  /**
   * Returns TRUE, if TargetedCode can be interpreted as primary targeted code.
   * Otherwise it should return FALSE.
   * @return bool
   */
  public function isPrimary();

  /**
   * Returns a string code representing the targeted code.
   * @return string|null
   */
  public function getCode();

}
