<?php

namespace Drupal\uhsg_samlauth;

use Drupal\Core\Path\PathValidator;
use Drupal\Core\Url;
use Drupal\samlauth\SamlService as OriginalSamlService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;

class SamlService extends OriginalSamlService {

  const SESS_VALUE_KEY = 'postLoginLogoutDestination';

  /**
   * @var Request
   */
  protected $request;

  /**
   * @var PathValidator
   */
  protected $pathValidator;

  /**
   * @param Request $request
   */
  public function setRequest(Request $request) {
    $this->request = $request;
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

    // Get session and create one if not exit
    if (!$this->request->hasSession()) {
      $this->request->setSession(new Session());
    }

    // Primarly get url from referrer if it is valid or else use front page.
    $referer = $this->request->server->get('HTTP_REFERER');
    $url = new Url('<front>');
    if ($referer) {
      if ($valid_url = $this->pathValidator->getUrlIfValid($referer)) {
        $url = $valid_url;
      }
    }

    // Store serialized URL into session
    $session = $this->request->getSession();
    $session->set(self::SESS_VALUE_KEY, serialize($url));
    $session->save();
  }

  /**
   * Get login and logout destinations in user´s session.
   *
   * @return Url|null
   */
  public function getPostLoginLogoutDestination() {
    if ($this->request->hasSession() && !empty($this->request->getSession()->get(self::SESS_VALUE_KEY))) {
      return unserialize($this->request->getSession()->get(self::SESS_VALUE_KEY));
    }
    return NULL;
  }

  /**
   * Removes post login/logout destination from existing session. Nothing is
   * done if request has no session.
   */
  public function removePostLoginLogoutDestination() {
    if ($this->request->hasSession()) {
      foreach ($this->request->getSession()->all() as $key => $value) {
        if ($key == self::SESS_VALUE_KEY) {
          $this->request->getSession()->remove(self::SESS_VALUE_KEY);
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

}
