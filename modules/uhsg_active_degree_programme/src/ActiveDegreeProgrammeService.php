<?php

/**
 * @file
 * Contains \Drupal\uhsg_active_degree_programme\ActiveDegreeProgrammeService.
 */
 
namespace Drupal\uhsg_active_degree_programme;

use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\flag\FlaggingInterface;
use Drupal\flag\FlagServiceInterface;
use Drupal\taxonomy\Entity\Term;
use Drupal\taxonomy\TermAccessControlHandler;
use Symfony\Component\HttpFoundation\RequestStack;

class ActiveDegreeProgrammeService {

  /**
   * The request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * The entity repository.
   *
   * @var \Drupal\Core\Entity\EntityRepositoryInterface
   */
  protected $entityRepository;

  /**
   * Used for querying by degree programme codes.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Stores the current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $user;

  /**
   * @var FlagServiceInterface
   */
  protected $flagService;

  /**
   * Stores the resolved term.
   *
   * @var \Drupal\taxonomy\Entity\Term|null
   */
  protected $resolvedTerm;

  /**
   * Specifies the entity type where degree programmes are stored.
   * @var string
   */
  protected $degreeProgrammeEntityType = 'taxonomy_term';

  /**
   * Specifies the bundle of entity type where degree programmes are.
   * @var string
   */
  protected $degreeProgrammeBundle = 'degree_programme';

  /**
   * ActiveDegreeProgrammeService constructor.
   * @param ConfigFactory $configFactory
   * @param RequestStack $requestStack
   * @param EntityRepositoryInterface $entityRepository
   * @param EntityTypeManagerInterface $entityTypeManager
   * @param AccountInterface $user
   * @param FlagServiceInterface $flagService
   */
  public function __construct(
    ConfigFactory $configFactory,
    RequestStack $requestStack,
    EntityRepositoryInterface $entityRepository,
    EntityTypeManagerInterface $entityTypeManager,
    AccountInterface $user,
    FlagServiceInterface $flagService) {

    $this->config = $configFactory->get('uhsg_active_degree_programme.settings');
    $this->requestStack = $requestStack;
    $this->entityRepository = $entityRepository;
    $this->entityTypeManager = $entityTypeManager;
    $this->user = $user;
    $this->flagService = $flagService;
    $this->resolvedTerm = NULL;
  }

  /**
   * Set active degree programme.
   * @param Term $term
   */
  public function set(Term $term) {
    $tid = $term->id();
    $cookie = ['degree_programme' => $tid];
    $this->saveCookie($cookie);
  }

  /**
   * Resets active degree programme.
   */
  public function reset() {
    $this->deleteCookie();
  }

  /**
   * Return name of active degree programme.
   * @return null|string
   */
  public function getName() {
    $term = $this->getTerm();

    return $term ? $this->entityRepository->getTranslationFromContext($term)->label() : NULL;
  }

  /**
   * Return the ID of the active degree programme.
   * @return int|null
   */
  public function getId() {
    $term = $this->getTerm();

    return $term ? $term->id() : NULL;
  }

  /**
   * Tries to get term ID from request.
   * @return string|null
   *   Returns an term ID or NULL, when not found.
   */
  protected function getTidFromQuery() {

    // If term id is given directly, return that
    $query_param_tid = $this->requestStack->getCurrentRequest()->get('degree_programme');
    if ($query_param_tid) {
      return $query_param_tid;
    }

    // If code is given, resolve its term ID first
    $query_param_code = $this->requestStack->getCurrentRequest()->get('degree_programme_code');
    if ($query_param_code) {
      return $this->resolveTidFromCode($query_param_code);
    }

    return NULL;
  }

  /**
   * @param $code string
   *
   * @return int|null
   *   Returns ID of taxonomy term or NULL if not found.
   */
  protected function resolveTidFromCode($code) {
    $entity_query = $this->entityTypeManager->getStorage($this->degreeProgrammeEntityType)->getQuery('AND');
    $entity_query->condition('field_code', $code);
    $entity_ids = $entity_query->execute();
    if (!empty($entity_ids)) {
      $ids = array_keys($entity_ids);
      return $ids[0];
    }
    return NULL;
  }

  /**
   * Return term of active degree programme.
   * @return \Drupal\taxonomy\Entity\Term|null
   */
  public function getTerm() {
    if (!is_null($this->resolvedTerm)) {
      return $this->resolvedTerm;
    }

    // Check from parameters.
    $query_param = $this->getTidFromQuery();
    if ($query_param) {
      $term = Term::load($query_param);
      if (!is_null($term) && $this->access($term) && $term->getVocabularyId() == $this->degreeProgrammeBundle) {
        $this->debug('Resolved by parameter ' . $term->id());
        $this->resolvedTerm = $term;
        return $this->resolvedTerm;
      }
      else {
        // If we can't load the term, then reset active degree programme.
        $this->reset();
      }
    }

    // Check from X-Headers.
    $degree_programme_from_headers = $this->requestStack->getCurrentRequest()->headers->get('x-degree-programme');
    if ($degree_programme_from_headers) {
      $term = Term::load($this->requestStack->getCurrentRequest()->headers->get('x-degree-programme'));
      if (!is_null($term) && $this->access($term) && $term->getVocabularyId() == $this->degreeProgrammeBundle) {
        $this->debug('Resolved by header ' . $term->id());
        $this->resolvedTerm = $term;
        return $this->resolvedTerm;
      }
      else {
        // If we can't load the term, then reset active degree programme.
        $this->reset();
      }
    }

    // Check from cookies.
    $degree_programme_from_cookies = $this->requestStack->getCurrentRequest()->cookies->get('Drupal_visitor_degree_programme');
    if ($degree_programme_from_cookies) {
      $term = Term::load($degree_programme_from_cookies);
      if (!is_null($term) && $this->access($term) && $term->getVocabularyId() == $this->degreeProgrammeBundle) {
        $this->debug('Resolved by cookie ' . $term->id());
        $this->resolvedTerm = $term;
        return $this->resolvedTerm;
      }
      else {
        // If we can't load the term, then reset active degree programme.
        $this->reset();
      }
    }

    // Check from flaggings.
    if ($this->user->isAuthenticated()) {
      $flag = $this->flagService->getFlagById('my_degree_programmes');
      /** @var FlaggingInterface[] $flaggings */
      $flaggings = $this->flagService->getFlagFlaggings($flag, $this->user);
      $primary_field_name = $this->config->get('primary_field_name');
      foreach ($flaggings as $flagging) {
        if ($flagging->hasField($primary_field_name) && !$flagging->get($primary_field_name)->isEmpty()) {
          if (!empty($flagging->get($primary_field_name)->first()->getValue()['value'])) {

            // This flagging is primary
            $entity = $flagging->getFlaggable();
            if (!is_null($entity) &&
                $entity->getEntityTypeId() == $this->degreeProgrammeEntityType &&
                $entity->getVocabularyId() == $this->degreeProgrammeBundle &&
                $this->access($entity)) {
              $this->debug('Resolved by cookie ' . $entity->id());
              $this->resolvedTerm = $entity;
              return $this->resolvedTerm;
            }
            else {
              // If we can't load the term, then reset active degree programme.
              $this->reset();
            }

          }
        }
      }
    }

    return $this->resolvedTerm;
  }

  /**
   * Checks whether user has access to view given term.
   * @param Term $term
   * @return bool
   */
  protected function access(Term $term) {
    $handler = new TermAccessControlHandler($term->getEntityType());
    return $handler->access($term, 'view', $this->user);
  }

  protected function deleteCookie() {
    user_cookie_delete('degree_programme');
  }

  protected function saveCookie($cookie) {
    user_cookie_save($cookie);
  }

  protected function debug($message) {
    \Drupal::logger('uhsg_active_degree_programme')->debug($message);
  }
}
