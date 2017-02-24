<?php

namespace Drupal\uhsg_user_sync\SamlAuth;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Session\AccountInterface;
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
   * @var EntityManagerInterface
   */
  protected $entityManager;

  public function __construct(ConfigFactoryInterface $configFactory, OprekServiceInterface $oprekService, FlagServiceInterface $flagService, EntityManagerInterface $entityManager) {
    $this->config = $configFactory->get('uhsg_user_sync.settings');
    $this->oprekService = $oprekService;
    $this->flagService = $flagService;
    $this->entityManager = $entityManager;
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
    $this->syncMyDegreeProgrammes($event->getAccount());
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
   * @param AccountInterface $account
   */
  protected function syncMyDegreeProgrammes(AccountInterface $account) {

    // Figure out student number.
    $field_name = $this->config->get('studentID_field_name');
    if (!$field_name) {
      // We don't know which field to look from
      return;
    }
    if (!$account->getFieldDefinition($field_name)) {
      // We can't find the configured field definition
      return;
    }

    // Clear out all existing technical degree programmes
    $this->clearTechnicalDegreeProgrammes($account);

    // When student number is available...
    if ($student_number = $account->get($field_name)->getString()) {
      $this->setTechnicalDegreeProgrammes($account, $student_number);
    }

  }

  /**
   * Clears my degree programmes that were managed programmatically.
   * @param AccountInterface $account
   * @return void
   */
  protected function clearTechnicalDegreeProgrammes(AccountInterface $account) {
    $flags = $this->flagService->getUsersFlags($account);
    foreach ($flags as $flag) {
      if ($flag->bundle() == 'my_degree_programmes') {
        $flaggings = $this->flagService->getFlagFlaggings($flag, $account);
        foreach ($flaggings as $flagging) {
          /** @var FlaggingInterface $flagging */
          if ($flagging->hasField($this->config->get('technical_condition_field_name'))) {
            if ($flagging->get($this->config->get('technical_condition_field_name'))->first()->getValue()) {
              // Deletes user´s flaggings that was programmatically created,
              // which is tracked by the hidden boolean field.
              $flagging->delete();
            }
          }
        }
        // Once we find my_degree_programme flags, there is no reason to keep
        // looping...
        break;
      }
    }
  }

  /**
   * Sets technical degree programmes based on student number.
   * @param AccountInterface $account
   * @param $student_number
   */
  protected function setTechnicalDegreeProgrammes(AccountInterface $account, $student_number) {

    // Collect all known degree programme codes, so we know which Terms we
    // should flag when getting matches.
    $known_degree_programmes = $this->getAllKnownDegreeProgrammes();

    // Map study rights to known degree programmes and create flaggings
    if ($study_rights = $this->oprekService->getStudyRights($student_number)) {
      foreach ($study_rights as $study_right) {
        foreach ($study_right->getElements() as $element) {
          if (isset($known_degree_programmes[$element->getCode()])) {

            // Flag the degree programme
            $flag = $this->flagService->getFlagById('my_degree_programmes');
            $this->flagService->flag($flag, $known_degree_programmes[$element->getCode()], $account);

            // Load the flagging, so we can set some field values
            /** @var FlaggingInterface[] $flaggings */
            $flaggings = $this->flagService->getEntityFlaggings($flag, $known_degree_programmes[$element->getCode()], $account);
            foreach ($flaggings as $flagging) {

              // If "technical condition" field exists, set it to TRUE
              if ($flagging->hasField($this->config->get('technical_condition_field_name'))) {
                $flagging->set($this->$this->config->get('technical_condition_field_name'), TRUE);

                // If study right is in 'primary' state and primary field
                // exists, then set the priary to TRUE.
                if ($study_right->getState() == StudyRight::STATE_PRIMARY && $flagging->hasField($this->config->get('primary_field_name'))) {
                  $flagging->set($this->config->get('primary_field_name'), TRUE);
                }

                // And save the flagging
                $flagging->save();
              }
            }
          }
        }
      }
    }

  }

  /**
   * Gets list of degree programmes as taxonomy terms.
   * @return Term[]
   */
  protected function getAllKnownDegreeProgrammes() {
    $known_degree_programmes = [];
    foreach ($this->entityManager->getStorage('taxonomy_term')->loadMultiple() as $term) {
      /** @var Term $term */
      if ($term->hasField($this->config->get('code_field_name'))) {
        $code = $term->get($this->config->get('code_field_name'))->first()->getString();
        $known_degree_programmes[$code] = $term;
      }
    }
    return $known_degree_programmes;
  }

}
