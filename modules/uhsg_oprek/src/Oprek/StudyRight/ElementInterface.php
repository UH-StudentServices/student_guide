<?php

namespace Drupal\uhsg_oprek\Oprek\StudyRight;

/**
 * Element describes the structure inheritence of StudyRight. With different set
 * of elements, a StudyRight can be presented either extensively or with very
 * specificly.
 */
interface ElementInterface {

  /**
   * Returns the code of the Study Right element.
   * @return string
   */
  public function getCode();

  /**
   * Returns the ID of the Study Right element.
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
