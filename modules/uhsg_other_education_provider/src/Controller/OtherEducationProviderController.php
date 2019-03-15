<?php

namespace Drupal\uhsg_other_education_provider\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Drupal\taxonomy\Entity\Term;
use Drupal\uhsg_other_education_provider\OtherEducationProviderService;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Controller routines for other education provider routes.
 */
class OtherEducationProviderController extends ControllerBase {

  /** @var \Drupal\uhsg_other_education_provider\OtherEducationProviderService*/
  protected $otherEducationProviderService;

  public function __construct(OtherEducationProviderService $otherEducationProviderService) {
    $this->otherEducationProviderService = $otherEducationProviderService;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('uhsg_other_education_provider.other_education_provider')
    );
  }

  public function setOtherEducationProvider($tid) {
    $term = $this->loadTerm($tid);

    if ($term) {
      $this->otherEducationProviderService->set($term);
    }

    return $this->doRedirect();
  }

  public function resetOtherEducationProvider() {
    $this->otherEducationProviderService->reset();
    $url = $this->getHttpReferer();
    $urlWithoutParameters = parse_url($url->toString(), PHP_URL_PATH);

    return new RedirectResponse($urlWithoutParameters);
  }

  private function getHttpReferer() {
    return Url::fromUri(\Drupal::request()->server->get('HTTP_REFERER', 'internal:/'));
  }

  /**
   * @param int $tid
   * @return \Drupal\taxonomy\Entity\Term
   */
  protected function loadTerm($tid) {
    return Term::load($tid);
  }

  /**
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   */
  protected function doRedirect() {
    $httpReferer = $this->getHttpReferer()->toString();
    $schemeAndHttpHost = \Drupal::request()->getSchemeAndHttpHost();
    $internal = strpos($httpReferer, $schemeAndHttpHost) !== FALSE;
    $frontPageUrl = Url::fromUri('internal:/')->toString();

    return $internal ? new RedirectResponse($httpReferer) : new RedirectResponse($frontPageUrl);
  }

}
