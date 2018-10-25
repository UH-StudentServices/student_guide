<?php

namespace Drupal\uhsg_oprek\Oprek\StudyRight;

/**
 * Represent StudyRight object.
 */
class StudyRight implements StudyRightInterface {

  use SortByElementIdTrait;

  /**
   * @var array
   */
  protected $properties;

  /**
   * Contains list of known possible states and their identified strings that we
   * look from the text values (might be in any language).
   * @var array
   */
  protected $knownStates = ['Optio' => StudyRightInterface::STATE_OPTION, 'Ensisijainen' => StudyRightInterface::STATE_PRIMARY];

  /**
   * Holds the single element list of TargetedCodes for static caching.
   * @var []TargetedCode
   */
  protected $targetedCodesSingles;

  /**
   * Holds the concatonated element list of TargetedCodes for static caching.
   * @var []TargetedCode
   */
  protected $targetedCodesConcatonated;

  /**
   * Holds the assembled list of TargetedCodes for static caching.
   * @var []TargetedCode
   */
  protected $targetedCodes;

  /**
   * Specifies the date/time which is used to filter out irrelevant parts of
   * information while determining study rights.
   * @var \DateTime
   */
  protected $date;

  /**
   * StudyRight constructor.
   * @param array $properties
   *   List of the properties of study rights given by the response of Oprek
   *   service.
   */
  public function __construct(array $properties) {
    $this->properties = $properties;
    $this->targetedCodesSingles = [];
    $this->targetedCodesConcatonated = [];
    $this->date = new \DateTime();
  }

  /**
   * Set the date that is used for filtering our irrelevant information.
   *
   * @param \DateTime $date
   */
  public function setDate(\DateTime $date) {
    $this->date = $date;
  }

  /**
   * {@inheritdoc}
   */
  public function getState() {
    if (!empty($this->properties['state'])) {
      /*
       * Example of structure we expect:
       * @code
       * [state] => Array
       *   (
       *       [0] => Array
       *           (
       *               [langcode] => fi
       *               [text] => Optio
       *           )
       *       [1] => Array
       *           (
       *               [langcode] => sv
       *               [text] => Option
       *           )
       *       [2] => Array
       *           (
       *               [langcode] => en
       *               [text] => Option
       *           )
       *   )
       * @endcode
       */
      foreach ($this->properties['state'] as $state) {
        if (!empty($this->knownStates[$state['text']])) {
          return $this->knownStates[$state['text']];
        }
      }
    }
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getElements() {
    $return = [];
    if (!empty($this->properties['elements']) && is_array($this->properties['elements'])) {
      foreach ($this->properties['elements'] as $element_raw) {
        $element = new Element($element_raw);
        $element->setDate($this->date);
        if ($element->isActive()) {
          $return[] = $element;
        }
      }
    }
    return $this->sortByElementIdAsc($return);
  }

  /**
   * {@inheritdoc}
   */
  public function getTargetedCodes() {
    if (is_null($this->targetedCodes)) {
      $this->targetedCodes = [];
      $this->assembleSingularTargetedCodes();
      $this->assembleConcatenatedTargetedCodes();
      $this->assembleTargetedCodes();
    }
    return $this->targetedCodes;
  }

  /**
   * Assembles each element as single targeted codes.
   * @return void
   */
  private function assembleSingularTargetedCodes() {
    foreach ($this->getElements() as $element) {
      if ($element->isTargetable()) {
        $targetedCode = new TargetedCode();
        $targetedCode->setElements([$element]);
        $this->targetedCodesSingles[] = $targetedCode;
      }
    }
  }

  /**
   * Assembles elements as concatonated targeted code.
   * @return void
   */
  private function assembleConcatenatedTargetedCodes() {
    $concatenatedElements = [];
    foreach ($this->getElements() as $element) {
      if ($element->isTargetable()) {
        $concatenatedElements[] = $element;
      }
    }
    if (count($concatenatedElements) > 1) {
      $targetedCode = new TargetedCode();
      $targetedCode->setElements($concatenatedElements);
      $this->targetedCodesConcatonated[] = $targetedCode;
    }
  }

  /**
   * Assigns an primary targeted code if there should be one.
   */
  private function assembleTargetedCodes() {
    if ($this->getState() == StudyRightInterface::STATE_PRIMARY) {
      if (empty($this->targetedCodesConcatonated) && !empty($this->targetedCodesSingles)) {
        // When no concatonated codes, then we simply tag first code
        foreach ($this->targetedCodesSingles as $index => $targetedCodesSingle) {
          $this->targetedCodesSingles[$index]->setPrimary(TRUE);
          break;
        }
      }
      elseif (!empty($this->targetedCodesConcatonated)) {
        // When concatonated codes, then we simply tag first code primary
        foreach ($this->targetedCodesConcatonated as $index => $targetedCodeConcatonated) {
          $this->targetedCodesConcatonated[$index]->setPrimary(TRUE);
          break;
        }
      }
    }

    // Finally merge both assembled into one array of targeted codes
    $this->targetedCodes = array_merge($this->targetedCodesSingles, $this->targetedCodesConcatonated);
  }

}
