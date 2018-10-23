<?php

namespace Drupal\uhsg_oprek\Oprek\StudyRight;

class StudyRight implements StudyRightInterface {

  /**
   * @var array
   */
  protected $properties;

  /**
   * Contains list of known possible states and their identified strings that we
   * look from the text values (might be in any language).
   * @var array
   */
  protected $knownStates = ['Optio' => self::STATE_OPTION, 'Ensisijainen' => self::STATE_PRIMARY];

  /**
   * StudyRight constructor.
   * @param array $properties
   *   List of the properties of study rights given by the response of Oprek
   *   service.
   */
  public function __construct(array $properties) {
    $this->properties = $properties;
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
      foreach ($this->properties['elements'] as $element) {
        $return[] = new Element($element);
      }
    }
    return $return;
  }

}
