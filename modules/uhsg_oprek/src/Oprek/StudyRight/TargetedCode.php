<?php

namespace Drupal\uhsg_oprek\Oprek\StudyRight;

class TargetedCode implements TargetedCodeInterface {

  /** @var bool*/
  protected $primary;

  /** @var array*/
  protected $elements;

  /** @var string|null*/
  protected $code;

  public function __construct() {
    $this->primary = FALSE;
    $this->elements = [];
  }

  /**
   * Set boolean whether this TargetedCode is primary or not.
   * @param bool $primary
   * @return void
   */
  public function setPrimary(bool $primary) {
    $this->primary = $primary;
  }

  /**
   * @return bool
   *   TRUE if TargetedCode is primary. FALSE otherwise.
   */
  public function isPrimary() {
    return $this->primary;
  }

  /**
   * Set elements, which TargetedCode will use for delivering the code.
   * @param []ElementInterface $elements
   *   List of ElementInterface elements.
   * @return int
   *   Count of elements.
   */
  public function setElements(array $elements) {
    $this->elements = $elements;
    return count($this->elements);
  }

  /**
   * @return string
   *   Get the code that can be used for targeting.
   */
  public function getCode() {
    if (empty($this->code)) {
      $this->code = '';
      foreach ($this->elements as $element) {
        $this->code .= $element->getCode();
      }
    }
    return $this->code;
  }

}
