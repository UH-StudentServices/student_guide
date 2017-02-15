<?php

namespace Drupal\uhsg_samlauth;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Path\PathValidator;
use Drupal\Core\Url;
use Drupal\externalauth\ExternalAuth;
use Drupal\samlauth\SamlService as OriginalSamlService;
use Drupal\uhsg_redirect_to_login\StackMiddleware\RedirectToLogin;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;

class SamlService extends OriginalSamlService {

  const SESS_VALUE_KEY = 'postLoginLogoutDestination';

  /**
   * @var RequestStack
   */
  protected $requestStack;

  /**
   * @var Session
   */
  protected $session;

  /**
   * @var PathValidator
   */
  protected $pathValidator;

  /**
   * Constructor for Drupal\uhsg_samlauth\SamlService.
   *
   * @param \Drupal\externalauth\ExternalAuth $external_auth
   *   The ExternalAuth service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The EntityTypeManager service.
   * @param \Psr\Log\LoggerInterface $logger
   *   A logger instance.
   * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $event_dispatcher
   *   Event dispatcher
   * @param \Symfony\Component\HttpFoundation\RequestStack
   *   Reuqest stack.
   * @param \Symfony\Component\HttpFoundation\Session\Session
   *   Session.
   * @param \Drupal\Core\Path\PathValidator
   *   Path validator.
   */
  public function __construct(ExternalAuth $external_auth, ConfigFactoryInterface $config_factory, EntityTypeManagerInterface $entity_type_manager, LoggerInterface $logger, EventDispatcherInterface $event_dispatcher, RequestStack $requestStack, Session $session, PathValidator $pathValidator) {
    parent::__construct($external_auth, $config_factory, $entity_type_manager, $logger, $event_dispatcher);
    $this->setRequestStack($requestStack);
    $this->setSession($session);
    $this->setPathValidator($pathValidator);
  }

  /**
   * @param RequestStack $requestStack
   */
  public function setRequestStack(RequestStack $requestStack) {
    $this->requestStack = $requestStack;
  }

  /**
   * @param Session $session
   */
  public function setSession(Session $session) {
    $this->session = $session;
  }

  /**
   * @param PathValidator $pathValidator
   */
  public function setPathValidator(PathValidator $pathValidator) {
    $this->pathValidator = $pathValidator;
  }

  /**
   * Set login and logout destinations in user´s session.
   */
  public function setPostLoginLogoutDestination() {

    // Ensure that session is started
    if (!$this->session->isStarted()) {
      $this->session->start();
    }

    // We default at least to our frontpage
    $url = new Url('<front>');

    // If we can catch the referrer, use that
    $referer = $this->requestStack->getCurrentRequest()->server->get('HTTP_REFERER');
    if ($referer) {
      if ($valid_url = $this->pathValidator->getUrlIfValid($referer)) {
        $url = $valid_url;
      }
    }

    // In conjunction with "Redirect to login" module, it sets the current URI
    // when it triggers login. We may catch the URI from the cookie.
    if ($this->requestStack->getCurrentRequest()->cookies->has(RedirectToLogin::COOKIE_NAME_TRIGGERED)) {
      $cookie_url = $this->requestStack->getCurrentRequest()->cookies->get(RedirectToLogin::COOKIE_NAME_TRIGGERED);
      if ($valid_url = $this->pathValidator->getUrlIfValid($cookie_url)) {
        $url = $valid_url;
      }
    }

    // Store the serialized URL into session.
    $this->session->set(self::SESS_VALUE_KEY, serialize($url));
    $this->session->save();
  }

  /**
   * Get login and logout destinations in user´s session.
   *
   * @return Url|null
   */
  public function getPostLoginLogoutDestination() {
    if (!empty($this->session->get(self::SESS_VALUE_KEY))) {
      return unserialize($this->session->get(self::SESS_VALUE_KEY));
    }
    return NULL;
  }

  /**
   * Removes post login/logout destination from existing session. Nothing is
   * done if request has no session.
   */
  public function removePostLoginLogoutDestination() {
    if ($this->session->isStarted()) {
      foreach ($this->session->all() as $key => $value) {
        if ($key == self::SESS_VALUE_KEY) {
          $this->session->remove(self::SESS_VALUE_KEY);
          break;
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getPostLoginDestination() {
    return $this->getPostLoginLogoutDestination();
  }

  /**
   * {@inheritdoc}
   */
  public function getPostLogoutDestination() {
    return $this->getPostLoginLogoutDestination();
  }

  /**
   * {@inheritdoc}
   */
  public function logout($return_to = null) {
    if (!$return_to) {
      $sp_config = $this->samlAuth->getSettings()->getSPData();
      $return_to = $sp_config['singleLogoutService']['url'];
    }
    user_logout();
    $return_to = $this->appendPostLogoutDestination($return_to);
    $this->samlAuth->logout($return_to, array('referrer' => $return_to));
  }

  /**
   * Adds an return URL into given URL address query.
   * @param $return_to
   * @return string
   */
  protected function appendPostLogoutDestination($return_to) {
    $url = Url::fromUri($return_to);
    $return_url = $this->getPostLogoutDestination();
    if ($return_url) {
      $query = $url->getOption('query') ?: [];
      $query['return'] = $return_url->setAbsolute(TRUE)->toString(TRUE)->getGeneratedUrl();
      $url->setOption('query', $query);
    }
    return $url->toString(TRUE)->getGeneratedUrl();
  }

}
