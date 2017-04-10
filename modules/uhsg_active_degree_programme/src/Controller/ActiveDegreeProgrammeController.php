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
    $url = $this->getHttpReferer();
    $url->setOptions(['query' => ['degree_programme' => $tid]]);

    return new RedirectResponse($url->toString());
  }

  public function resetActiveDegreeProgramme() {
    $this->activeDegreeProgrammeService->reset();
    $url = $this->getHttpReferer();
    $urlWithoutParameters = parse_url($url->toString(), PHP_URL_PATH);

    return new RedirectResponse($urlWithoutParameters);
  }

  private function getHttpReferer() {
    return Url::fromUri(\Drupal::request()->server->get('HTTP_REFERER', 'internal:/'));
  }

  /**
   * @param int $tid
   * @return Term
   */
  protected function loadTerm($tid) {
    return Term::load($tid);
  }
}
