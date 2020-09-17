<?php

namespace Drupal\uhsg_oprek\Oprek\StudyRight;

/**
 * Represents StudyRight object.
 */
class StudyRight implements StudyRightInterface {

  use SortByElementIdTrait;

  /**
   * @var array
   */
  protected $properties;

  /**
   * Contains a list of known states and their identifier strings that we look
   * from the text values (might be in any language).
   * @var array
   */
  protected $knownStates = ['Optio' => StudyRightInterface::STATE_OPTION, 'Ensisijainen' => StudyRightInterface::STATE_PRIMARY];

  /**
   * Holds the single element list of TargetedCodes for static caching.
   * @var TargetedCode[]
   */
  protected $targetedCodesSingles;

  /**
   * Holds the concatenated element list of TargetedCodes for static caching.
   * @var TargetedCode[]
   */
  protected $targetedCodesConcatonated;

  /**
   * Holds the assembled list of TargetedCodes for static caching.
   * @var TargetedCode[]
   */
  protected $targetedCodes;

  /**
   * Specifies the date/time that is used to filter out irrelevant parts of
   * information while determining study rights.
   * @var \DateTime
   */
  protected $date;

  /**
   * StudyRight constructor.
   * @param array $properties
   *   List of the properties of study rights from the Oprek service response.
   */
  public function __construct(array $properties) {
    $this->properties = $properties;
    $this->targetedCodesSingles = [];
    $this->targetedCodesConcatonated = [];
    $this->date = new \DateTime();
  }

  /**
   * Set the date that is used for filtering out irrelevant information.
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
        $state = (array) $state;
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
        $element_raw = (array) $element_raw;
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
   * Assembles elements as concatenated targeted code.
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
   * Assigns a primary targeted code if there should be one.
   */
  private function assembleTargetedCodes() {
    if ($this->getState() == StudyRightInterface::STATE_PRIMARY) {
      if (empty($this->targetedCodesConcatonated) && !empty($this->targetedCodesSingles)) {
        // When no concatenated codes, then we simply tag first code
        foreach ($this->targetedCodesSingles as $index => $targetedCodesSingle) {
          $this->targetedCodesSingles[$index]->setPrimary(TRUE);
          break;
        }
      }
      elseif (!empty($this->targetedCodesConcatonated)) {
        // When concatenated codes, then we simply tag first code primary
        foreach ($this->targetedCodesConcatonated as $index => $targetedCodeConcatonated) {
          $this->targetedCodesConcatonated[$index]->setPrimary(TRUE);
          break;
        }
      }
    }

    // Finally merge both assembled into one array of targeted codes
    $this->targetedCodes = array_merge($this->targetedCodesSingles, $this->targetedCodesConcatonated);

    // Log the codes to make corner case debugging simpler.
    $targetedCodes = array(
      'targetedCodes' => (array) $this->targetedCodes,
      'targetedCodesSingles' => (array) $this->targetedCodesSingles,
      'targetedCodesConcatonated' => (array) $this->targetedCodesConcatonated,
    );
    \Drupal::logger('uhsg_oprek')->info('StudyRights/TargetedCodes were parsed for uid %uid as : <pre>@targeted_codes</pre>', [
      '%uid' => \Drupal::currentUser()->id(),
      '@targeted_codes' => print_r($targetedCodes, TRUE),
    ]);
  }

}
