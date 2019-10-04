<?php

namespace Drupal\uhsg_samlauth\Controller;

use Drupal\Core\Routing\TrustedRedirectResponse;
use Drupal\samlauth\Controller\SamlController as OriginalSamlController;
use Drupal\uhsg_samlauth\SamlService;
use Symfony\Component\HttpFoundation\RedirectResponse;

class SamlController extends OriginalSamlController {

  /**
   * {@inheritdoc}
   */
  public function __construct(SamlService $saml) {
    parent::__construct($saml);
    $this->saml = $saml;
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
      $this->messenger()->addError($e->getMessage());
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

    $url = $this->saml->getPostLogoutDestination()->toString(TRUE);
    $response = new TrustedRedirectResponse($url->getGeneratedUrl());
    $response->addCacheableDependency($url);
    $this->saml->removePostLoginLogoutDestination();
    return $response;
  }

}
