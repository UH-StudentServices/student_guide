<?php

namespace Drupal\uhsg_oprek\Oprek\StudyRight;

class Element implements ElementInterface {

  /**
   * @var array
   */
  protected $properties;

  /**
   * Element constructor.
   * @param array $properties
   */
  public function __construct(array $properties) {
    $this->properties = $properties;
  }

  /**
   * Returns the code of the element.
   * @return string
   */
  public function getCode() {
    if (!empty($this->properties['code']) && is_string($this->properties['code'])) {
      return $this->properties['code'];
    }
    return '';
  }

}
