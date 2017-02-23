<?php

namespace Drupal\uhsg_oprek\Oprek\StudyRight;

interface StudyRightInterface {

  /**
   * Return the state of the element.
   * @return string
   */
  public function getState();

  /**
   * Returns list of StudyRightElement items.
   * @return Element[]
   */
  public function getElements();

}
