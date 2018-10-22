<?php

namespace Drupal\uhsg_oprek\Oprek\StudyRight;

class Element implements ElementInterface {

  /** @var array*/
  protected $properties;

  /** @var array*/
  protected $targetableElementIds;

  /**
   * Element constructor.
   * @param array $properties
   */
  public function __construct(array $properties) {
    $this->properties = $properties;
    $this->targetableElementIds = [20, 30];
  }

  /**
   * {@inheritdoc}
   */
  public function getCode() {
    if (!empty($this->properties['code']) && is_string($this->properties['code'])) {
      return $this->properties['code'];
    }
    return '';
  }

  /**
   * Return element ID.
   * @return int|null
   */
  public function getId() {
    if (!empty($this->properties['element_id']) && is_numeric($this->properties['element_id'])) {
      return (int) $this->properties['element_id'];
    }
    return NULL;
  }

  /**
   * Return TRUE, when element ID matches predefined targetable element IDs.
   * @return bool
   */
  public function isTargetable() {
    return in_array($this->getId(), $this->targetableElementIds);
  }

}
