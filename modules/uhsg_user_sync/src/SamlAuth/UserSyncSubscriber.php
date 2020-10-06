<?php

namespace Drupal\uhsg_user_sync\SamlAuth;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannel;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\flag\FlagInterface;
use Drupal\flag\FlagServiceInterface;
use Drupal\samlauth\Event\SamlAuthEvents;
use Drupal\samlauth\Event\SamlAuthUserSyncEvent;
use Drupal\uhsg_oprek\Oprek\OprekServiceInterface;
use Drupal\uhsg_sisu\Services\StudyRightsService;
use Drupal\uhsg_samlauth\AttributeParser;
use Drupal\uhsg_samlauth\AttributeParserInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Site\Settings;

/**
 * Synchronise relevant user attributes given by SAML authentication during SAML
 * authentication login.
 */
class UserSyncSubscriber implements EventSubscriberInterface {

  use StringTranslationTrait;

  /**
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected $config;

  /**
   * @var \Drupal\uhsg_oprek\Oprek\OprekServiceInterface
   */
  protected $oprekService;

  /**
   * @var Drupal\uhsg_sisu\Services\StudyRightsService
   */
  protected $studyRightsService;

  /**
   * @var \Drupal\flag\FlagServiceInterface
   */
  protected $flagService;

  /**
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * @var \Drupal\Core\Logger\LoggerChannel
   */
  protected $logger;

  /**
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * Add debug logging?
   * This can be overridden in settings.local.php with:
   *   $settings['uhsg_oprek_add_debug_logging'] = TRUE;
   *
   * @var bool
   */
  const UHSG_OPREK_ADD_DEBUG_LOGGING = FALSE;

  public function __construct(ConfigFactoryInterface $configFactory, OprekServiceInterface $oprekService, StudyRightsService $studyRightsService, FlagServiceInterface $flagService, EntityTypeManagerInterface $entityTypeManager, LoggerChannel $logger, MessengerInterface $messenger) {
    $this->config = $configFactory->get('uhsg_user_sync.settings');
    $this->oprekService = $oprekService;
    $this->studyRightsService = $studyRightsService;
    $this->flagService = $flagService;
    $this->entityTypeManager = $entityTypeManager;
    $this->logger = $logger;
    $this->messenger = $messenger;
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
    $this->syncOodiUid($event, $attributes);
    $this->syncStudentId($event, $attributes);
    $this->syncCommonName($event, $attributes);

    try {
      $this->syncMyDegreeProgrammes($event);
    }
    catch (\Exception $e) {
      $this->logger->error($this->t('Could not get degree programmes. Error: @error (code @code)', ['@error' => $e->getMessage(), '@code' => $e->getCode()]));
      $this->messenger->addMessage($this->t('There is a problem with the connection to Oodi and your degree programmes cannot be shown.'), 'warning');
    }
  }

  /**
   * Synchronises Oodi UID field.
   * @param \Drupal\samlauth\Event\SamlAuthUserSyncEvent $event
   * @param \Drupal\uhsg_samlauth\AttributeParserInterface $attributes
   */
  protected function syncOodiUid(SamlAuthUserSyncEvent $event, AttributeParserInterface $attributes) {

    // Specify what is the name of the field we want to set Oodi UID to?
    $field_name = $this->config->get('oodiUID_field_name');
    if (!$field_name) {
      return;
    }

    // If specified field definition has been found
    if ($event->getAccount()->getFieldDefinition($field_name)) {
      $previous_value = $event->getAccount()->get($field_name)->getString();
      $new_value = $attributes->getOodiUid();
      if ($new_value && $new_value != $previous_value) {
        // When we have new value and it's different from previous value, it
        // means that we need to update it to the account.
        $event->getAccount()->get($field_name)->setValue($new_value);
        $event->markAccountChanged();
      }
      elseif ($previous_value && !$new_value) {
        // When we don't have new value but previous value, it means that
        // Oodi UID has/must been removed.
        $event->getAccount()->get($field_name)->setValue(NULL);
        $event->markAccountChanged();
      }
    }
  }

  /**
   * Synchronises student ID field.
   * @param \Drupal\samlauth\Event\SamlAuthUserSyncEvent $event
   * @param \Drupal\uhsg_samlauth\AttributeParserInterface $attributes
   */
  protected function syncStudentId(SamlAuthUserSyncEvent $event, AttributeParserInterface $attributes) {

    // Specify what is the name of the field we want to set student ID to?
    $field_name = $this->config->get('studentID_field_name');
    if (!$field_name) {
      return;
    }

    // If specified field definition has been found
    if ($event->getAccount()->getFieldDefinition($field_name)) {
      $previous_value = $event->getAccount()->get($field_name)->getString();
      $new_value = $attributes->getStudentId();
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
   * Synchronises common name field.
   * @param \Drupal\samlauth\Event\SamlAuthUserSyncEvent $event
   * @param \Drupal\uhsg_samlauth\AttributeParserInterface $attributes
   */
  protected function syncCommonName(SamlAuthUserSyncEvent $event, AttributeParserInterface $attributes) {

    // Specify what is the name of the field we want to set student ID to?
    $field_name = $this->config->get('common_name_field_name');
    if (!$field_name) {
      return;
    }

    // If specified field definition has been found
    if ($event->getAccount()->getFieldDefinition($field_name)) {
      $previous_value = $event->getAccount()->get($field_name)->getString();
      $new_value = $attributes->getCommonName();
      if ($new_value && $new_value != $previous_value) {
        // When we have new value and it's different from previous value, it
        // means that we need to update it to the account.
        $event->getAccount()->get($field_name)->setValue($new_value);
        $event->markAccountChanged();
      }
      elseif ($previous_value && !$new_value) {
        // When we don't have new value but previous value, it means that
        // common name has/must been removed.
        $event->getAccount()->get($field_name)->setValue(NULL);
        $event->markAccountChanged();
      }
    }
  }

  /**
   * Synchronises my degree programmes.
   * @param \Drupal\samlauth\Event\SamlAuthUserSyncEvent $event
   */
  protected function syncMyDegreeProgrammes(SamlAuthUserSyncEvent $event) {

    // Figure out student number.
    $field_name = $this->config->get('oodiUID_field_name');
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
   * @param \Drupal\samlauth\Event\SamlAuthUserSyncEvent $event
   * @return bool
   */
  protected function clearTechnicalDegreeProgrammes(SamlAuthUserSyncEvent $event) {
    $flags = $this->getUsersFlags($event->getAccount(), 'taxonomy_term', 'degree_programme');
    $cleared = 0;
    $technical_condition_field_name = $this->config->get('technical_condition_field_name');

    foreach ($flags as $flag) {
      if ($flag->bundle() == 'my_degree_programmes') {
        $flaggings = $this->getFlagFlaggings($flag, $event->getAccount());
        foreach ($flaggings as $flagging) {
          /** @var \Drupal\flag\Entity\FlaggingInterface $flagging */
          if ($flagging->hasField($technical_condition_field_name)) {
            if ($flagging->get($technical_condition_field_name)->first()->getValue()) {
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

  protected function getUsersFlags(AccountInterface $account, $entity_type = NULL, $bundle = NULL) {
    $filtered_flags = [];

    if ($account->isAuthenticated()) {
      $flags = $this->flagService->getAllFlags($entity_type, $bundle);

      foreach ($flags as $flag_id => $flag) {
        if ($flag->actionAccess('flag', $account)->isAllowed() ||
            $flag->actionAccess('unflag', $account)->isAllowed()) {
          $filtered_flags[$flag_id] = $flag;
        }
      }
    }

    return $filtered_flags;
  }

  public function getFlagFlaggings(FlagInterface $flag, AccountInterface $account = NULL, $session_id = NULL) {
    $flaggingStorage = $this->entityTypeManager->getStorage('flagging');
    $query = $flaggingStorage->getQuery();

    $query->condition('flag_id', $flag->id());

    if (!empty($account) && !$flag->isGlobal()) {
      $query->condition('uid', $account->id());

      if ($account->isAnonymous()) {
        if (empty($session_id)) {
          throw new \LogicException('An anonymous user must be identifed by session ID.');
        }

        $query->condition('session_id', $session_id);
      }
    }

    $ids = $query->execute();

    return $flaggingStorage->loadMultiple($ids);
  }

  /**
   * Sets technical degree programmes based on student number.
   * @param \Drupal\samlauth\Event\SamlAuthUserSyncEvent $event
   * @param $student_number
   * @return bool
   */
  protected function setTechnicalDegreeProgrammes(SamlAuthUserSyncEvent $event, $student_number) {

    // Collect all known degree programme codes, so we know which Terms we
    // should flag when getting matches.
    $known_degree_programmes = $this->getAllKnownDegreeProgrammes();
    $known_degree_programmes_array = (array) $known_degree_programmes;
    $known_degree_programme_keys = array_keys($known_degree_programmes_array);

    // Keep track of new technical degree programmes
    $added = 0;

    // Map StudentDegreeProgram to a known degree programme and create flagging
    if($studyrights = $this->studyRightsService->getActiveStudyRights($student_number)) {
      $technical_condition_field_name = $this->config->get('technical_condition_field_name');
      $primary_field_name = $this->config->get('primary_field_name');

      // Loop trough all studyrights
      foreach($studyrights as $studyright) {
        if (!empty($studyright) && Settings::get('uhsg_oprek_add_debug_logging', self::UHSG_OPREK_ADD_DEBUG_LOGGING)) {
          // Debug (a lot of) study right data. Enable only temporarily.
          \Drupal::logger('uhsg_oprek')->info('setTechnicalDegreeProgrammes(),
            targeted codes are: <pre>@targeted_codes</pre> and
            degree_programmes: <pre>@degree_programmes</pre>', [
            '@targeted_codes' => print_r($studyright->getCode(), TRUE),
            '@degree_programmes' => print_r($known_degree_programme_keys, TRUE),
          ]);
        }

        // If code matches our degree_program code then proceed
        if (isset($known_degree_programmes[$studyright->getCode()])) {

          // Flag the degree programme
          $flag = $this->flagService->getFlagById('my_degree_programmes');

          // Get potentially existing flagging, if not exist, then create.
          /** @var \Drupal\flag\Entity\Flagging $flagging */
          $flagging = $this->flagService->getFlagging($flag, $known_degree_programmes[$studyright->getCode()], $event->getAccount());
          if (!$flagging) {
            $flagging = $this->flagService->flag($flag, $known_degree_programmes[$studyright->getCode()], $event->getAccount());
          }

          // Load the flagging, so we can set some field values
          // If "technical condition" field exists, set it to TRUE
          if ($flagging->hasField($technical_condition_field_name)) {
            $flagging->set($technical_condition_field_name, TRUE);

            // If targeted code is 'primary' and primary field exists, then
            // set the primary to TRUE.
            if ($studyright->isPrimary() && $flagging->hasField($primary_field_name)) {
              $flagging->set($primary_field_name, TRUE);
            }

            // And save the flagging
            $flagging->save();
            $added++;
          }
        }
      }
    }

    // Return TRUE if any flags were created.
    return $added > 0;
  }

  /**
   * Gets list of degree programmes as taxonomy terms.
   * @return \Drupal\taxonomy\Entity\Term[]
   */
  protected function getAllKnownDegreeProgrammes() {
    $known_degree_programmes = [];
    $code_field_name = $this->config->get('code_field_name');

    foreach ($this->entityTypeManager->getStorage('taxonomy_term')->loadMultiple() as $term) {
      /** @var \Drupal\taxonomy\Entity\Term $term */
      if ($term->hasField($code_field_name) && !$term->get($code_field_name)->isEmpty()) {
        $code = $term->get($code_field_name)->first()->getString();
        $known_degree_programmes[$code] = $term;
      }
    }
    return $known_degree_programmes;
  }

}
