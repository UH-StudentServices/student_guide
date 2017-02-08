<?php

namespace Drupal\uhsg_samlauth\Controller;

use Drupal\Core\Path\PathValidator;
use Drupal\Core\Routing\TrustedRedirectResponse;
use Drupal\samlauth\Controller\SamlController as OriginalSamlController;
use Drupal\uhsg_samlauth\SamlService;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;

class SamlController extends OriginalSamlController {

  /**
   * @var RequestStack
   */
  protected $requestStack;

  /**
   * @var PathValidator
   */
  protected $pathValidator;

  /**
   * {@inheritdoc}
   */
  public function __construct(SamlService $saml, RequestStack $requestStack, PathValidator $pathValidator) {
    $this->saml = $saml;
    parent::__construct($saml);
    $this->saml->setRequestStack($requestStack);
    $this->saml->setPathValidator($pathValidator);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('samlauth.saml'),
      $container->get('request_stack'),
      $container->get('path.validator')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function login() {
    $this->saml->setPostLoginLogoutDestination();
    parent::login();
  }

  /**
   * {@inheritdoc}
   */
  public function logout() {
    $this->saml->setPostLoginLogoutDestination();
    parent::logout();
  }

  /**
   * {@inheritdoc}
   */
  public function acs() {
    try {
      $this->saml->acs();
    }
    catch (\Exception $e) {
      drupal_set_message($e->getMessage(), 'error');
      return new RedirectResponse('/');
    }

    $url = $this->saml->getPostLoginDestination()->toString(TRUE);
    $response = new TrustedRedirectResponse($url->getGeneratedUrl());
    $response->addCacheableDependency($url);
    $this->saml->removePostLoginLogoutDestination();
    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function sls() {
    $this->saml->sls();

    $url = $this->saml->getPostLoginDestination()->toString(TRUE);
    $response = new TrustedRedirectResponse($url->getGeneratedUrl());
    $response->addCacheableDependency($url);
    $this->saml->removePostLoginLogoutDestination();
    return $response;
  }

}
