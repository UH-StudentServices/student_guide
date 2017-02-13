<?php

namespace Drupal\uhsg_redirect_to_login\StackMiddleware;

use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class RedirectToLogin implements HttpKernelInterface {

  const COOKIE_NAME_LOGGED = 'OPINTONI_HAS_LOGGED_IN';
  const COOKIE_NAME_TRIGGERED = 'OPINTONI_HAS_LOGGED_IN_HAS_TRIGGERED';

  /**
   * The wrapped HTTP kernel.
   *
   * @var \Symfony\Component\HttpKernel\HttpKernelInterface
   */
  protected $httpKernel;

  public function __construct(HttpKernelInterface $httpKernel) {
    $this->httpKernel = $httpKernel;
  }

  /**
   * {@inheritdoc}
   */
  public function handle(Request $request, $type = self::MASTER_REQUEST, $catch = TRUE) {
    if ($type == self::MASTER_REQUEST && $this->hasLoggedIn($request) && !$this->hasTriggered($request)) {
      $response = $this->redirectToLoginResponse($request, $type, $catch);
    }
    else {
      $response = $this->httpKernel->handle($request, $type, $catch);
    }
    return $response;
  }

  /**
   * Determine from the request, whether client has been logged in
   * Opetukseni/Opintoni service.
   *
   * @param Request $request
   * @return bool
   */
  protected function hasLoggedin(Request $request) {
    return $request->cookies->has(self::COOKIE_NAME_LOGGED);
  }

  /**
   * Determine from the request, whether client has triggered login redirect
   * before.
   *
   * @param Request $request
   * @return bool
   */
  protected function hasTriggered(Request $request) {
    return $request->cookies->has(self::COOKIE_NAME_TRIGGERED);
  }

  /**
   * Returns an redirect response to login.
   *
   * @param Request $request
   * @param $type
   * @param $catch
   * @return \Symfony\Component\HttpFoundation\Response
   */
  protected function redirectToLoginResponse(Request $request, $type, $catch) {

    // When cookie has been found, but not yet triggered, create redirect
    // response.
    $response = new RedirectResponse('/saml/login');

    // Create triggered cookie, so that following requests wouldn't redirect
    $response->headers->setCookie(new Cookie(self::COOKIE_NAME_TRIGGERED, 'yes'));

    return $response;

  }
}
