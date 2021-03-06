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
  protected $aliasMapping = [
    'commonName' => 'urn:oid:2.5.4.3',
    'hyPersonId' => 'urn:oid:1.3.6.1.4.1.18869.1.1.1.48',
    'studentId' => 'urn:oid:1.3.6.1.4.1.25178.1.2.14',
    'employeeId' => 'urn:oid:2.16.840.1.113730.3.1.3',
    'userId' => 'urn:oid:0.9.2342.19200300.100.1.1',
    'emailAddress' => 'urn:oid:0.9.2342.19200300.100.1.3',
    'logoutUrl' => 'urn:mace:funet.fi:haka:logout-url',
    'groups' => 'urn:mace:funet.fi:helsinki.fi:hyGroupCn',
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
    if (empty($this->aliasMapping[$alias])) {
      return NULL;
    }
    $key = $this->aliasMapping[$alias];

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
    return (string) $value[0];
  }

  /**
   * {@inheritdoc}
   */
  public function getEmailAddress() {
    $value = $this->getAttributeValueFromAlias('emailAddress');
    if (is_null($value)) {
      return '';
    }
    return (string) $value[0];
  }

  /**
   * {@inheritdoc}
   */
  public function getStudentId() {
    $value = $this->getAttributeValueFromAlias('studentId');
    if (is_null($value)) {
      return '';
    }

    /*
     * Student IDs are in format "{prefix}{ID}" where {ID} represents
     * placeholder for the actual value. Therefore we need some preprocessing
     * prior to return the actual value.
     *
     * This attribute might return multiple values. We'll pick the first one
     * with proper UH-specific urn-prefix.
     *
     * @see $this->studentIdPrefix
     */
    foreach ($value as $urn) {
      $urn = (string) $urn;
      if (mb_strpos($urn, $this->studentIdPrefix) === 0) {
        // Value has a valid prefix so we're ok to return the id part.
        return substr($urn, mb_strlen($this->studentIdPrefix));
      }
    }

    return '';
  }

  /**
   * {@inheritdoc}
   */
  public function getEmployeeId() {
    $value = $this->getAttributeValueFromAlias('employeeId');
    if (is_null($value)) {
      return '';
    }
    return (string) $value[0];
  }

  /**
   * {@inheritdoc}
   */
  public function getHyPersonId() {
    $value = $this->getAttributeValueFromAlias('hyPersonId');
    if (is_null($value)) {
      return '';
    }
    return (string) $value[0];
  }

  /**
   * {@inheritdoc}
   */
  public function getUserId() {
    $value = $this->getAttributeValueFromAlias('userId');
    if (is_null($value)) {
      return '';
    }
    return (string) $value[0];
  }

  /**
   * {@inheritdoc}
   */
  public function getLogoutUrl() {
    $value = $this->getAttributeValueFromAlias('logoutUrl');
    if (is_null($value)) {
      return '';
    }
    return (string) $value[0];
  }

  /**
   * {@inheritdoc}
   */
  public function getGroups() {
    $value = $this->getAttributeValueFromAlias('groups');
    if (is_null($value)) {
      return [];
    }
    return $value;
  }

}
