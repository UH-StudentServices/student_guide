<?php

namespace Drupal\uhsg_oprek\Oprek\StudyRight;

/**
 * StudyRight represents what associated person can study. StudyRights can be
 * represented with elements that include codes separately or with targeted
 * codes that are used for content targeting.
 */
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

  /**
   * Returns a list of targeted codes assembled internally using elements.
   * @return TargetedCodeInterface[]
   */
  public function getTargetedCodes();

}
