<?php

namespace Drupal\uhsg_samlauth\Controller;

use Drupal\Core\Path\PathValidator;
use Drupal\Core\Routing\TrustedRedirectResponse;
use Drupal\samlauth\Controller\SamlController as OriginalSamlController;
use Drupal\uhsg_samlauth\SamlService;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class SamlController extends OriginalSamlController {

  /**
   * @var Request
   */
  protected $request;

  /**
   * @var PathValidator
   */
  protected $pathValidator;

  /**
   * {@inheritdoc}
   */
  public function __construct(SamlService $saml) {
    $this->saml = $saml;
    parent::__construct($saml);

    // TODO: Why I can't inject these from constructor? Is it because
    // constructor signature differs from the original constructor?
    $this->saml->setRequest(\Drupal::request());
    $this->saml->setPathValidator(\Drupal::pathValidator());
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
