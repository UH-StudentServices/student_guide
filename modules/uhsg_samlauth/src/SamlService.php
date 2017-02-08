<?php

namespace Drupal\uhsg_samlauth;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Path\PathValidator;
use Drupal\Core\Url;
use Drupal\externalauth\ExternalAuth;
use Drupal\samlauth\SamlService as OriginalSamlService;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;

class SamlService extends OriginalSamlService {

  const SESS_VALUE_KEY = 'postLoginLogoutDestination';

  /**
   * @var RequestStack
   */
  protected $requestStack;

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
   * @param \Symfony\Component\HttpFoundation\RequestStack
   *   Reuqest stack.
   * @param \Drupal\Core\Path\PathValidator
   *   Path validator.
   */
  public function __construct(ExternalAuth $external_auth, ConfigFactoryInterface $config_factory, EntityTypeManagerInterface $entity_type_manager, LoggerInterface $logger, RequestStack $requestStack, PathValidator $pathValidator) {
    parent::__construct($external_auth, $config_factory, $entity_type_manager, $logger);
    $this->setRequestStack($requestStack);
    $this->setPathValidator($pathValidator);
  }

  /**
   * @param RequestStack $requestStack
   */
  public function setRequestStack(RequestStack $requestStack) {
    $this->requestStack = $requestStack;
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

    // Get session. Create the session if it does not exist.
    if (!$this->requestStack->getCurrentRequest()->hasSession()) {
      $this->requestStack->getCurrentRequest()->setSession(new Session());
    }

    // Get the URL from the referer if it is valid or else use front page.
    $referer = $this->requestStack->getCurrentRequest()->server->get('HTTP_REFERER');
    $url = new Url('<front>');
    if ($referer) {
      if ($valid_url = $this->pathValidator->getUrlIfValid($referer)) {
        $url = $valid_url;
      }
    }

    // Store the serialized URL into session.
    $session = $this->requestStack->getCurrentRequest()->getSession();
    $session->set(self::SESS_VALUE_KEY, serialize($url));
    $session->save();
  }

  /**
   * Get login and logout destinations in user´s session.
   *
   * @return Url|null
   */
  public function getPostLoginLogoutDestination() {
    if ($this->requestStack->getCurrentRequest()->hasSession() && !empty($this->requestStack->getCurrentRequest()->getSession()->get(self::SESS_VALUE_KEY))) {
      return unserialize($this->requestStack->getCurrentRequest()->getSession()->get(self::SESS_VALUE_KEY));
    }
    return NULL;
  }

  /**
   * Removes post login/logout destination from existing session. Nothing is
   * done if request has no session.
   */
  public function removePostLoginLogoutDestination() {
    if ($this->requestStack->getCurrentRequest()->hasSession()) {
      foreach ($this->requestStack->getCurrentRequest()->getSession()->all() as $key => $value) {
        if ($key == self::SESS_VALUE_KEY) {
          $this->requestStack->getCurrentRequest()->getSession()->remove(self::SESS_VALUE_KEY);
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
    $query = $url->getOption('query');
    $query['return'] = $this->getPostLogoutDestination()->setAbsolute(TRUE)->toString(TRUE)->getGeneratedUrl();
    $url->setOption('query', $query);
    return $url->toString(TRUE)->getGeneratedUrl();
  }

}
