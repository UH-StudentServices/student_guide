<?php

namespace Drupal\uhsg_oprek\Oprek\StudyRight;

/**
 * Element describes the structure inheritance of StudyRight. With different set
 * of elements, a StudyRight can be broad or specific.
 */
interface ElementInterface {

  /**
   * Returns the code of the StudyRight element.
   * @return string
   */
  public function getCode();

  /**
   * Returns the ID of the StudyRight element.
   * @return int
   */
  public function getId();

  /**
   * Tells if given Element is "targetable", meaning that this element's code
   * should be part of the StudyRight's targeted code.
   * @return bool
   */
  public function isTargetable();

}
