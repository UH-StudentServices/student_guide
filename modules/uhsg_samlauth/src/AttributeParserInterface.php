<?php

namespace Drupal\uhsg_samlauth;

interface AttributeParserInterface {

  /**
   * Return an common name from the attributes.
   * @return string
   */
  public function getCommonName();

  /**
   * Returns an email address from the attributes.
   * @return string
   */
  public function getEmailAddress();

  /**
   * Returns an student ID from the attributes.
   * @return string
   */
  public function getStudentID();

  /**
   * Returns an Oodi UID from the attributes.
   * @return string
   */
  public function getOodiUid();

  /**
   * Returns an user ID in Oodi service from the attributes.
   * @return string
   */
  public function getUserId();

  /**
   * Returns an logout URL address from the attributes.
   * @return string
   */
  public function getLogoutUrl();

}
