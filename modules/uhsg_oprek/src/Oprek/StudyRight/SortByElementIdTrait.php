<?php

namespace Drupal\uhsg_oprek\Oprek\StudyRight;

trait SortByElementIdTrait {

  /**
   * Takes in collection of elements and returns them sorted by element ID from
   * lowest number to highest (ascending).
   * @param array $elements
   * @return array
   */
  protected function sortByElementIdAsc(array $elements) {
    if (!empty($elements)) {
      usort($elements, ['self', 'compareElementId']);
    }
    return $elements;
  }

  /**
   * Sorting callback which will sort by element ID ascending.
   * @param Element $elementA
   * @param Element $elementB
   * @return int
   */
  public static function compareElementId(Element $elementA, Element $elementB) {
    if (!empty($elementA->getId()) && !empty($elementB->getId())) {
      if ($elementA->getId() != $elementB->getId()) {
        return ($elementA->getId() < $elementB->getId()) ? -1 : 1;
      }
    }
    return 0;
  }

}
