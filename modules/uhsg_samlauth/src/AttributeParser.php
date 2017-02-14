<?php

namespace Drupal\uhsg_samlauth;

class AttributeParser implements AttributeParserInterface {

  /**
   * Stores the attributes array where all attribute values are returned from.
   * @var array
   */
  protected $attributes = [];

  /**
   * Contains attribute mapping array.
   * @var array
   */
  protected $alias_mapping = [
    'commonName' => 'urn:oid:2.5.4.3',
    'oodiUid' => '1.3.6.1.4.1.18869.1.1.1.32',
    'studentId' => 'urn:oid:1.3.6.1.4.1.25178.1.2.14',
    'userId' => 'urn:oid:0.9.2342.19200300.100.1.1',
    'emailAddress' => 'urn:oid:0.9.2342.19200300.100.1.3',
    'logoutUrl' => 'urn:mace:funet.fi:haka:logout-url',
  ];

  /**
   * StudentID has a prefix prior to actual value. This string represents the
   * expected prefix.
   * @var string
   */
  protected $studentIdPrefix = 'urn:schac:personalUniqueCode:int:studentID:helsinki.fi:';

  /**
   * AttributeParser constructor.
   * @param array $attributes
   */
  public function __construct(array $attributes) {
    $this->attributes = $attributes;
  }

  /**
   * Returns attribute value from given alias.
   * @param $alias
   * @return mixed|null
   */
  protected function getAttributeValueFromAlias($alias) {

    // Resolve alias key
    if (!empty($this->alias_mapping[$alias])) {
      return NULL;
    }
    $key = $this->alias_mapping[$alias];

    // Get value
    if (!empty($this->attributes[$key])) {
      return $this->attributes[$key];
    }

    return NULL;

  }

  /**
   * {@inheritdoc}
   */
  public function getCommonName() {
    $value = $this->getAttributeValueFromAlias('commonName');
    if (is_null($value)) {
      return '';
    }
    return (string) $value;
  }

  /**
   * {@inheritdoc}
   */
  public function getEmailAddress() {
    $value = $this->getAttributeValueFromAlias('emailAddress');
    if (is_null($value)) {
      return '';
    }
    return (string) $value;
  }

  /**
   * {@inheritdoc}
   */
  public function getStudentID() {
    $value = $this->getAttributeValueFromAlias('studentId');
    if (is_null($value)) {
      return '';
    }

    /*
     * Student IDs are in format "{prefix}{ID}" where {ID} represents
     * placeholder for the actual value. Therefore we need some preprocessing
     * prior to return the actual value.
     */
    $value = (string) $value;
    if (mb_strlen($value) > mb_strlen($this->studentIdPrefix)) {
      // OK the value seems to have an sensible string length so return it
      // without the expected prefix.
      return substr($value, mb_strlen($this->studentIdPrefix));
    }

    return '';
  }

  /**
   * {@inheritdoc}
   */
  public function getOodiUid() {
    $value = $this->getAttributeValueFromAlias('oodiUid');
    if (is_null($value)) {
      return '';
    }
    return (string) $value;
  }

  /**
   * {@inheritdoc}
   */
  public function getUserId() {
    $value = $this->getAttributeValueFromAlias('userId');
    if (is_null($value)) {
      return '';
    }
    return (string) $value;
  }

  /**
   * {@inheritdoc}
   */
  public function getLogoutUrl() {
    $value = $this->getAttributeValueFromAlias('logoutUrl');
    if (is_null($value)) {
      return '';
    }
    return (string) $value;
  }

}
