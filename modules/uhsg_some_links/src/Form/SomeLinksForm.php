<?php

namespace Drupal\uhsg_some_links\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for Some links edit forms.
 *
 * @ingroup uhsg_some_links
 */
class SomeLinksForm extends ContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $entity = &$this->entity;

    $status = parent::save($form, $form_state);

    switch ($status) {
      case SAVED_NEW:
        drupal_set_message($this->t('Created the %label Some links.', [
          '%label' => $entity->label(),
        ]));
        break;

      default:
        drupal_set_message($this->t('Saved the %label Some links.', [
          '%label' => $entity->label(),
        ]));
    }
    $form_state->setRedirect('entity.some_links.canonical', ['some_links' => $entity->id()]);
  }

}
