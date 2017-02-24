<?php

namespace Drupal\uhsg_user_sync\SamlAuth;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\flag\Entity\Flagging;
use Drupal\flag\FlaggingInterface;
use Drupal\flag\FlagServiceInterface;
use Drupal\samlauth\Event\SamlAuthEvents;
use Drupal\samlauth\Event\SamlAuthUserSyncEvent;
use Drupal\taxonomy\Entity\Term;
use Drupal\uhsg_oprek\Oprek\OprekServiceInterface;
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

  /**
   * @var FlagServiceInterface
   */
  protected $flagService;

  /**
   * @var EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  public function __construct(ConfigFactoryInterface $configFactory, OprekServiceInterface $oprekService, FlagServiceInterface $flagService, EntityTypeManagerInterface $entityTypeManager) {
    $this->config = $configFactory->get('uhsg_user_sync.settings');
    $this->oprekService = $oprekService;
    $this->flagService = $flagService;
    $this->entityTypeManager = $entityTypeManager;
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
    $this->syncMyDegreeProgrammes($event);
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
   */
  protected function syncMyDegreeProgrammes(SamlAuthUserSyncEvent $event) {

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

    // Clear out all existing technical degree programmes
    $cleared = $this->clearTechnicalDegreeProgrammes($event);

    // When student number is available...
    $added = FALSE;
    if ($student_number = $event->getAccount()->get($field_name)->getString()) {
      $added = $this->setTechnicalDegreeProgrammes($event, $student_number);
    }

    // Mark account has changed, if cleared or added degree programmes
    if ($cleared || $added) {
      $event->markAccountChanged();
    }

  }

  /**
   * Clears my degree programmes that were managed programmatically.
   * @param SamlAuthUserSyncEvent $event
   * @return bool
   */
  protected function clearTechnicalDegreeProgrammes(SamlAuthUserSyncEvent $event) {
    $flags = $this->flagService->getUsersFlags($event->getAccount());
    $cleared = 0;
    foreach ($flags as $flag) {
      if ($flag->bundle() == 'my_degree_programmes') {
        $flaggings = $this->flagService->getFlagFlaggings($flag, $event->getAccount());
        foreach ($flaggings as $flagging) {
          /** @var FlaggingInterface $flagging */
          if ($flagging->hasField($this->config->get('technical_condition_field_name'))) {
            if ($flagging->get($this->config->get('technical_condition_field_name'))->first()->getValue()) {
              // Deletes user´s flaggings that was programmatically created,
              // which is tracked by the hidden boolean field.
              $flagging->delete();
              $cleared++;
            }
          }
        }
        // Once we find my_degree_programme flags, there is no reason to keep
        // looping...
        break;
      }
    }

    // Return TRUE if any technical degree programmes were cleared.
    return $cleared > 0;
  }

  /**
   * Sets technical degree programmes based on student number.
   * @param SamlAuthUserSyncEvent $event
   * @param $student_number
   * @return bool
   */
  protected function setTechnicalDegreeProgrammes(SamlAuthUserSyncEvent $event, $student_number) {

    // Collect all known degree programme codes, so we know which Terms we
    // should flag when getting matches.
    $known_degree_programmes = $this->getAllKnownDegreeProgrammes();

    // Keep track of new technical degree programmes
    $added = 0;

    // Map study rights to known degree programmes and create flaggings
    if ($study_rights = $this->oprekService->getStudyRights($student_number)) {
      foreach ($study_rights as $study_right) {
        foreach ($study_right->getElements() as $element) {
          if (isset($known_degree_programmes[$element->getCode()])) {

            // Flag the degree programme
            $flag = $this->flagService->getFlagById('my_degree_programmes');
            /** @var Flagging $flagging */
            $flagging = $this->flagService->flag($flag, $known_degree_programmes[$element->getCode()], $event->getAccount());

            // Load the flagging, so we can set some field values
            // If "technical condition" field exists, set it to TRUE
            if ($flagging->hasField($this->config->get('technical_condition_field_name'))) {
              $flagging->set($this->config->get('technical_condition_field_name'), TRUE);

              // If study right is in 'primary' state and primary field
              // exists, then set the priary to TRUE.
              if ($study_right->getState() == StudyRight::STATE_PRIMARY && $flagging->hasField($this->config->get('primary_field_name'))) {
                $flagging->set($this->config->get('primary_field_name'), TRUE);
              }

              // And save the flagging
              $flagging->save();
              $added++;
            }
          }
        }
      }
    }

    // Return TRUE if any flags were created.
    return $added > 0;

  }

  /**
   * Gets list of degree programmes as taxonomy terms.
   * @return Term[]
   */
  protected function getAllKnownDegreeProgrammes() {
    $known_degree_programmes = [];
    foreach ($this->entityTypeManager->getStorage('taxonomy_term')->loadMultiple() as $term) {
      /** @var Term $term */
      if ($term->hasField($this->config->get('code_field_name')) && !$term->get($this->config->get('code_field_name'))->isEmpty()) {
        $code = $term->get($this->config->get('code_field_name'))->first()->getString();
        $known_degree_programmes[$code] = $term;
      }
    }
    return $known_degree_programmes;
  }

}
