<?php

/**
 * @file
 * Contains \Drupal\uhsg_active_degree_programme\ActiveDegreeProgrammeService.
 */
 
namespace Drupal\uhsg_active_degree_programme;

use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Session\AccountInterface;
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
   * ActiveDegreeProgrammeService constructor.
   * @param RequestStack $requestStack
   * @param EntityRepositoryInterface $entityRepository
   * @param AccountInterface $user
   */
  public function __construct(RequestStack $requestStack, EntityRepositoryInterface $entityRepository, AccountInterface $user) {
    $this->requestStack = $requestStack;
    $this->entityRepository = $entityRepository;
    $this->user = $user;
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
    if ($term) {
      return $this->entityRepository->getTranslationFromContext($term)->label();
    }
  }

  /**
   * Return id of active degree programme.
   * @return int|mixed|null|string
   */
  public function getId() {
    $term = $this->getTerm();
    if ($term) {
      return $term->id();
    }
  }

  /**
   * Return term of active degree programme.
   * @return \Drupal\taxonomy\Entity\Term|null
   */
  public function getTerm() {
    if (!is_null($this->resolvedTerm)) {
      return $this->resolvedTerm;
    }

    // First check from parameters
    $query_param = $this->requestStack->getCurrentRequest()->get('degree_programme');
    if ($query_param) {
      $term = Term::load($query_param);
      if (!is_null($term) && $this->access($term)) {
        $this->debug('Resolved by parameter ' . $term->id());
        $this->resolvedTerm = $term;
        return $this->resolvedTerm;
      }
      else {
        // If we can't load the term, then reset active degree programme.
        $this->reset();
      }
    }

    // Secondly check from X-Headers
    $degree_programme_from_headers = $this->requestStack->getCurrentRequest()->headers->get('x-degree-programme');
    if ($degree_programme_from_headers) {
      $term = Term::load($this->requestStack->getCurrentRequest()->headers->get('x-degree-programme'));
      if (!is_null($term) && $this->access($term)) {
        $this->debug('Resolved by header ' . $term->id());
        $this->resolvedTerm = $term;
        return $this->resolvedTerm;
      }
      else {
        // If we can't load the term, then reset active degree programme.
        $this->reset();
      }
    }

    // Thirdly check from cookies
    $degree_programme_from_cookies = $this->requestStack->getCurrentRequest()->cookies->get('Drupal_visitor_degree_programme');
    if ($degree_programme_from_cookies) {
      $term = Term::load($degree_programme_from_cookies);
      if (!is_null($term) && $this->access($term)) {
        $this->debug('Resolved by cookie ' . $term->id());
        $this->resolvedTerm = $term;
        return $this->resolvedTerm;
      }
      else {
        // If we can't load the term, then reset active degree programme.
        $this->reset();
      }
    }

    // TODO: Fourthly check from logged in user study rights

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
