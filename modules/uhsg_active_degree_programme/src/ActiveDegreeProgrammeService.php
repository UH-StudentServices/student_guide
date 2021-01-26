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
use Drupal\flag\FlagInterface;
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
   * @var \Drupal\flag\FlagServiceInterface
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
   * Service for resolving degree programme codes into term IDs.
   * @var DegreeProgrammeCodeResolverService
   */
  protected $degreeProgrammeCodeResolver;

  /**
   * ActiveDegreeProgrammeService constructor.
   * @param \Drupal\Core\Config\ConfigFactory $configFactory
   * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entityRepository
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   * @param \Drupal\Core\Session\AccountInterface $user
   * @param \Drupal\flag\FlagServiceInterface $flagService
   * @param DegreeProgrammeCodeResolverService degreeProgrammeCodeResolver
   */
  public function __construct(
    ConfigFactory $configFactory,
    RequestStack $requestStack,
    EntityRepositoryInterface $entityRepository,
    EntityTypeManagerInterface $entityTypeManager,
    AccountInterface $user,
    FlagServiceInterface $flagService,
    DegreeProgrammeCodeResolverService $degreeProgrammeCodeResolver) {

    $this->config = $configFactory->get('uhsg_active_degree_programme.settings');
    $this->requestStack = $requestStack;
    $this->entityRepository = $entityRepository;
    $this->entityTypeManager = $entityTypeManager;
    $this->user = $user;
    $this->flagService = $flagService;
    $this->resolvedTerm = NULL;
    $this->degreeProgrammeCodeResolver = $degreeProgrammeCodeResolver;
  }

  /**
   * Return true if we should not filter based on degree_program.
   * @return bool
   */
  public function isAll() {
    $return = FALSE;

    // Fetch degree programme from query and headers
    $degree_programme_from_query = $this->requestStack->getCurrentRequest()->get('degree_programme');

    // If degree_programme is set to 'all' well return 'TRUE'
    if($degree_programme_from_query == "all") {
      $return = TRUE;
    }

    return $return;
  }

  /**
   * Set active degree programme.
   * @param \Drupal\taxonomy\Entity\Term $term
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
   * Return the code of the active degree programme.
   * @return string|null
   */
  public function getCode() {
    $term = $this->getTerm();

    return $term ? $term->get('field_code')->getString() : NULL;
  }

  /**
   * Return the user group of the active degree programme.
   * @return string|null
   */
  public function getUserGroup() {
    $term = $this->getTerm();

    return $term ? $term->get('field_user_group')->getString() : NULL;
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
      return $this->degreeProgrammeCodeResolver->resolveTidFromCode($query_param_code);
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
      if (!is_null($term) && $this->access($term) && $term->bundle() == $this->degreeProgrammeBundle) {
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
      if (!is_null($term) && $this->access($term) && $term->bundle() == $this->degreeProgrammeBundle) {
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
      if (!is_null($term) && $this->access($term) && $term->bundle() == $this->degreeProgrammeBundle) {
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
      /** @var \Drupal\flag\FlaggingInterface[] $flaggings */
      $flaggings = $this->getFlagFlaggings($flag, $this->user);
      $primary_field_name = $this->config->get('primary_field_name');
      foreach ($flaggings as $flagging) {
        if ($flagging->hasField($primary_field_name) && !$flagging->get($primary_field_name)->isEmpty()) {
          if (!empty($flagging->get($primary_field_name)->first()->getValue()['value'])) {

            // This flagging is primary
            $entity = $flagging->getFlaggable();
            if (!is_null($entity) &&
                $entity->getEntityTypeId() == $this->degreeProgrammeEntityType &&
                $entity->bundle() == $this->degreeProgrammeBundle &&
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

  protected function getFlagFlaggings(FlagInterface $flag, AccountInterface $account = NULL, $session_id = NULL) {
    $flaggingStorage = $this->entityTypeManager->getStorage('flagging');
    $query = $flaggingStorage->getQuery();

    $query->condition('flag_id', $flag->id());

    if (!empty($account) && !$flag->isGlobal()) {
      $query->condition('uid', $account->id());

      if ($account->isAnonymous()) {
        if (empty($session_id)) {
          throw new \LogicException('An anonymous user must be identified by session ID.');
        }

        $query->condition('session_id', $session_id);
      }
    }

    $ids = $query->execute();

    return $flaggingStorage->loadMultiple($ids);
  }

  /**
   * Checks whether user has access to view given term.
   * @param \Drupal\taxonomy\Entity\Term $term
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
