<?php

namespace Drupal\uhsg_user_sync\SamlAuth;

use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\samlauth\Event\SamlAuthEvents;
use Drupal\samlauth\Event\SamlAuthUserSyncEvent;
use Drupal\uhsg_oprek\Oprek\OprekServiceInterface;
use Drupal\uhsg_oprek\Oprek\StudyRight\Element;
use Drupal\uhsg_oprek\Oprek\StudyRight\StudyRight;
use Drupal\uhsg_samlauth\AttributeParser;
use Drupal\uhsg_samlauth\AttributeParserInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class UserSyncSubscriber implements EventSubscriberInterface {

  /**
   * @var ImmutableConfig
   */
  protected $config;

  /**
   * @var OprekServiceInterface
   */
  protected $oprekService;

  public function __construct(ConfigFactory $configFactory, OprekServiceInterface $oprekService) {
    $this->config = $configFactory->get('uhsg_user_sync.settings');
    $this->oprekService = $oprekService;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[SamlAuthEvents::USER_SYNC][] = ['onUserSync'];
    return $events;
  }

  public function onUserSync(SamlAuthUserSyncEvent $event) {
    $attributes = new AttributeParser($event->getAttributes());
    $this->syncStudentID($event, $attributes);
    $this->syncMyDegreeProgrammes($event, $attributes);
  }

  /**
   * Syncrhonises student ID field.
   * @param SamlAuthUserSyncEvent $event
   * @param AttributeParserInterface $attributes
   */
  protected function syncStudentID(SamlAuthUserSyncEvent $event, AttributeParserInterface $attributes) {

    // Specify what is the name of the field we want to set student ID to?
    $field_name = $this->config->get('studentID_field_name');
    if (!$field_name) {
      return;
    }

    // If specified field definition has been found
    if ($event->getAccount()->getFieldDefinition($field_name)) {
      $previous_value = $event->getAccount()->get($field_name)->getString();
      $new_value = $attributes->getStudentID();
      if ($new_value && $new_value != $previous_value) {
        // When we have new value and it's different from previous value, it
        // means that we need to update it to the account.
        $event->getAccount()->get($field_name)->setValue($new_value);
        $event->markAccountChanged();
      }
      elseif ($previous_value && !$new_value) {
        // When we don't have new value but previous value, it means that
        // student ID has/must been removed.
        $event->getAccount()->get($field_name)->setValue(NULL);
        $event->markAccountChanged();
      }
    }
  }

  /**
   * Synchronises my degree programmes.
   * @param SamlAuthUserSyncEvent $event
   * @param AttributeParserInterface $attributes
   */
  protected function syncMyDegreeProgrammes(SamlAuthUserSyncEvent $event, AttributeParserInterface $attributes) {

    // Figure out student number.
    $field_name = $this->config->get('studentID_field_name');
    if (!$field_name) {
      // We don't know which field to look from
      return;
    }
    if (!$event->getAccount()->getFieldDefinition($field_name)) {
      // We can't find the configured field definition
      return;
    }

    // TODO: First of all, clear out all existing technical degree programmes

    // When student number is available...
    if ($student_number = $event->getAccount()->get($field_name)->getString()) {

      // TODO: Collect all known degree programme codes in Drupal
      $known_degree_programmes = [];

      // Map study rights to known degree programmes
      if ($study_rights = $this->oprekService->getStudyRights($student_number)) {
        foreach ($study_rights as $study_right) {
          /** @var StudyRight $study_right */
          $state = $study_right->getState();
          foreach ($study_right->getElements() as $element) {
            /** @var Element $element */
            if (isset($known_degree_programmes[$element->getCode()])) {
              // TODO: Create an flagging based on code and state
            }
          }
        }
      }
    }

  }

}
