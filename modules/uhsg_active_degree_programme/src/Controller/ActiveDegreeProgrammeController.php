<?php

namespace Drupal\uhsg_active_degree_programme\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Drupal\taxonomy\Entity\Term;
use Drupal\uhsg_active_degree_programme\ActiveDegreeProgrammeService;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Controller routines for active degree programme routes.
 */
class ActiveDegreeProgrammeController extends ControllerBase {

  /** @var ActiveDegreeProgrammeService */
  protected $activeDegreeProgrammeService;

  public function __construct(ActiveDegreeProgrammeService $activeDegreeProgrammeService) {
    $this->activeDegreeProgrammeService = $activeDegreeProgrammeService;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('uhsg_active_degree_programme.active_degree_programme')
    );
  }

  public function setActiveDegreeProgramme($tid) {
    $term = $this->loadTerm($tid);
    if ($term) {
      $this->activeDegreeProgrammeService->set($term);
    }
    $url = Url::fromUri(\Drupal::request()->server->get('HTTP_REFERER'));
    $url->setOptions(['query' => ['degree_programme' => $tid]]);
    $status = 302;
    return new RedirectResponse($url->toString(), $status);
  }

  /**
   * @param int $tid
   * @return Term
   */
  protected function loadTerm($tid) {
    return Term::load($tid);
  }
}
