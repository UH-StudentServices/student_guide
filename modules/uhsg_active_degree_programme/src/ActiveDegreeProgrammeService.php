<?php

/**
 * @file
 * Contains \Drupal\uhsg_active_degree_programme\ActiveDegreeProgrammeService.
 */
 
namespace Drupal\uhsg_active_degree_programme;

use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\taxonomy\Entity\Term;
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
   * ActiveDegreeProgrammeService constructor.
   * @param RequestStack $requestStack
   * @param EntityRepositoryInterface $entityRepository
   */
  public function __construct(RequestStack $requestStack, EntityRepositoryInterface $entityRepository) {
    $this->requestStack = $requestStack;
    $this->entityRepository = $entityRepository;
  }

  /**
   * Set active degree programme.
   */
  public function set(Term $term) {
    $tid = $term->id();
    $cookie = ['degree_programme' => $tid];
    user_cookie_save($cookie);
  }

  /**
   * Return name of active degree programme.
   */
  public function getName() {
    $term = $this->getTerm();
    if ($term) {
      return $this->entityRepository->getTranslationFromContext($term)->getName();
    }
  }

  /**
   * Return id of active degree programme.
   */
  public function getId() {
    $term = $this->getTerm();
    if ($term) {
      return $term->id();
    }
  }

  /**
   * Return term of active degree programme.
   */
  public function getTerm() {

    // First check from parameters
    $query_param = $this->requestStack->getCurrentRequest()->get('degree_programme');
    if ($query_param) {
      $term = Term::load($query_param);
      return $term;
    }

    // Secondly check from X-Headers
    if (!empty($_SERVER['HTTP_X_DEGREE_PROGRAMME'])) {
      $term = Term::load($_SERVER['HTTP_X_DEGREE_PROGRAMME']);
      return $term;
    }

    // Thirdly check from cookies
    if (isset($_COOKIE['Drupal_visitor_degree_programme'])) {
      $term = Term::load($_COOKIE['Drupal_visitor_degree_programme']);
      return $term;
    }

    // TODO: Fourthly check from logged in user study rights

    return NULL;
  }
}
