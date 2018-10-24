<?php

namespace Drupal\uhsg_oprek\Oprek\StudyRight;

class Element implements ElementInterface {

  /** @var array*/
  protected $properties;

  /** @var array*/
  protected $targetableElementIds;

  /** @var \DateTime*/
  protected $date;

  /**
   * Element constructor.
   * @param array $properties
   */
  public function __construct(array $properties) {
    $this->properties = $properties;
    $this->targetableElementIds = [20, 30];
    $this->date = new \DateTime();
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

  /**
   * @param DateTime $date
   * @return void
   */
  public function setDate(\DateTime $date) {
    $this->date = $date;
  }

  /**
   * Returns TRUE, when this element can be evaluated to be active. This is
   * computed by comparing given date (by Element->setDate()) to start and end
   * dates.
   * @throws \Exception
   * @return bool
   */
  public function isActive() {
    if (empty($this->date)) {
      throw new Exception('No date set for determining active element. Use Element->setDate() before calling Element->isActive().');
    }

    // Require start and end date
    if (empty($this->properties['start_date'])) {
      throw new Exception('Missing start date.');
    }
    elseif (empty($this->properties['end_date'])) {
      throw new Exception('Missing end date.');
    }

    $start_date = new \DateTime($this->properties['start_date']);
    $end_date = new \DateTime($this->properties['end_date']);

    return ($start_date < $this->date && $end_date > $this->date);
  }

}
