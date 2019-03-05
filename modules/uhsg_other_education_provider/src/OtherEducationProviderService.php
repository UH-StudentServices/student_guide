<?php

/**
 * @file
 * Contains \Drupal\uhsg_other_education_provider\OtherEducationProviderService.
 */

namespace Drupal\uhsg_other_education_provider;

use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\taxonomy\Entity\Term;
use Drupal\taxonomy\TermAccessControlHandler;
use Symfony\Component\HttpFoundation\RequestStack;

class OtherEducationProviderService {

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
   * Stores the current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $user;

  /**
   * Stores the resolved term.
   *
   * @var \Drupal\taxonomy\Entity\Term|null
   */
  protected $resolvedTerm;

  /**
   * Specifies the entity type where other education providers are stored.
   * @var string
   */
  protected $otherEducationProviderEntityType = 'taxonomy_term';

  /**
   * Specifies the bundle of entity type where other education providers are.
   * @var string
   */
  protected $otherEducationProviderBundle = 'other_education_provider';

  /**
   * OtherEducationProviderService constructor.
   * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entityRepository
   * @param \Drupal\Core\Session\AccountInterface $user
   */
  public function __construct(
    RequestStack $requestStack,
    EntityRepositoryInterface $entityRepository,
    AccountInterface $user) {
    
    $this->requestStack = $requestStack;
    $this->entityRepository = $entityRepository;
    $this->user = $user;
    $this->resolvedTerm = NULL;
  }

  /**
   * Set other education provider.
   * @param \Drupal\taxonomy\Entity\Term $term
   */
  public function set(Term $term) {
    $tid = $term->id();
    $cookie = ['other_education_provider' => $tid];
    $this->saveCookie($cookie);
  }

  /**
   * Resets other education provider.
   */
  public function reset() {
    $this->deleteCookie();
  }

  /**
   * Return name of other education provider.
   * @return null|string
   */
  public function getName() {
    $term = $this->getTerm();

    return $term ? $this->entityRepository->getTranslationFromContext($term)->label() : NULL;
  }

  /**
   * Return the ID of the other education provider.
   * @return int|null
   */
  public function getId() {
    $term = $this->getTerm();

    return $term ? $term->id() : NULL;
  }

  /**
   * Return the code of the other education provider.
   * @return string|null
   */
  public function getCode() {
    $term = $this->getTerm();

    return $term ? $term->get('field_code')->getString() : NULL;
  }

  /**
   * Tries to get term ID from request.
   * @return string|null
   *   Returns an term ID or NULL, when not found.
   */
  protected function getTidFromQuery() {

    // If term id is given directly, return that
    $query_param_tid = $this->requestStack->getCurrentRequest()->get('other_education_provider');
    if ($query_param_tid) {
      return $query_param_tid;
    }

    return NULL;
  }

  /**
   * Return term of other education provider.
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
      if (!is_null($term) && $this->access($term) && $term->getVocabularyId() == $this->otherEducationProviderBundle) {
        $this->debug('Resolved by parameter ' . $term->id());
        $this->resolvedTerm = $term;
        return $this->resolvedTerm;
      }
      else {
        // If we can't load the term, then reset other education provider.
        $this->reset();
      }
    }

    // Check from X-Headers.
    $other_education_provider_from_headers = $this->requestStack->getCurrentRequest()->headers->get('x-other-education-provider');
    if ($other_education_provider_from_headers) {
      $term = Term::load($other_education_provider_from_headers);
      if (!is_null($term) && $this->access($term) && $term->getVocabularyId() == $this->otherEducationProviderBundle) {
        $this->debug('Resolved by header ' . $term->id());
        $this->resolvedTerm = $term;
        return $this->resolvedTerm;
      }
      else {
        // If we can't load the term, then reset other education provider.
        $this->reset();
      }
    }

    // Check from cookies.
    $other_education_provider_from_cookies = $this->requestStack->getCurrentRequest()->cookies->get('Drupal_visitor_other_education_provider');
    if ($other_education_provider_from_cookies) {
      $term = Term::load($other_education_provider_from_cookies);
      if (!is_null($term) && $this->access($term) && $term->getVocabularyId() == $this->otherEducationProviderBundle) {
        $this->debug('Resolved by cookie ' . $term->id());
        $this->resolvedTerm = $term;
        return $this->resolvedTerm;
      }
      else {
        // If we can't load the term, then reset other education provider.
        $this->reset();
      }
    }

    return $this->resolvedTerm;
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
    user_cookie_delete('other_education_provider');
  }

  protected function saveCookie($cookie) {
    user_cookie_save($cookie);
  }

  protected function debug($message) {
    \Drupal::logger('uhsg_other_education_provider')->debug($message);
  }

}
