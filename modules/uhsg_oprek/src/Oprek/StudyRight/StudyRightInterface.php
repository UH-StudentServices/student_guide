<?php

namespace Drupal\uhsg_oprek\Oprek\StudyRight;

interface StudyRightInterface {

  const STATE_PRIMARY = 'primary';
  const STATE_OPTION = 'option';

  /**
   * Return the state of the element. If unidentified state, then return NULL.
   * @return string|null
   */
  public function getState();

  /**
   * Returns list of StudyRightElement items.
   * @return Element[]
   */
  public function getElements();

}
