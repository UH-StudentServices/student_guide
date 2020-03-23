<?php

namespace Drupal\uhsg_news\Validate;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form API callback. Validate datetime element value.
 */
class DateTimeConstraint {

  /**
   * Validates given datetime element; notify about missing time portion
   * separately.
   *
   * @param array $element
   *   The form element whose value is being validated.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   * @param array $form
   *   The complete form structure.
   */
  public static function validateDateTime(array &$element, FormStateInterface $form_state, array &$form) {
    $input_exists = FALSE;
    $input = NestedArray::getValue($form_state->getValues(), $element['#parents'], $input_exists);
    if ($input_exists) {
      $title = !empty($element['#title']) ? $element['#title'] : '';

      // If there's a date input but an empty time input and time input is
      // expected, set an error.
      if (!empty($input['date']) && $element['#date_time_element'] != 'none' && empty($input['time'])) {
        $form_state->setError($element, t('The %field time is required.', ['%field' => $title]));
      }
    }
  }

}
