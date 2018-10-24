<?php

use Drupal\Tests\UnitTestCase;
use Drupal\uhsg_redirect_to_login\StackMiddleware\RedirectToLogin;
use Prophecy\Argument;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * @group uhsg
 */
class RedirectToLoginTest extends UnitTestCase {

  /** @var \Symfony\Component\HttpKernel\HttpKernelInterface*/
  private $httpKernel;

  /** @var \Symfony\Component\DependencyInjection\ParameterBag\ParameterBag*/
  private $cookies;

  /** @var \Drupal\uhsg_redirect_to_login\StackMiddleware\RedirectToLogin*/
  private $redirectToLogin;

  /** @var \Symfony\Component\HttpFoundation\Request*/
  private $request;

  public function setUp() {
    parent::setUp();

    $this->httpKernel = $this->prophesize(HttpKernelInterface::class);
    $this->cookies = $this->prophesize(ParameterBag::class);

    $this->request = $this->prophesize(Request::class);
    $this->request->cookies = $this->cookies;

    $this->redirectToLogin = new RedirectToLogin($this->httpKernel->reveal());
  }

  /**
   * @test
   */
  public function shouldNotRedirectWhenTheRequestIsNotMasterRequest() {
    $this->httpKernel->handle(Argument::any(), Argument::any(), Argument::any())->shouldBeCalled();

    $response = $this->redirectToLogin->handle($this->request->reveal(), HttpKernelInterface::SUB_REQUEST);

    $this->assertNotInstanceOf(RedirectResponse::class, $response);
  }

  /**
   * @test
   */
  public function shouldNotRedirectWhenTheLoginCookieDoesNotExist() {
    $this->httpKernel->handle(Argument::any(), Argument::any(), Argument::any())->shouldBeCalled();

    $response = $this->redirectToLogin->handle($this->request->reveal());

    $this->assertNotInstanceOf(RedirectResponse::class, $response);
  }

  /**
   * @test
   */
  public function shouldNotRedirectWhenTheLoginRedirectHasBeenTriggered() {
    $this->cookies->has(RedirectToLogin::COOKIE_NAME_LOGGED)->willReturn(TRUE);
    $this->cookies->has(RedirectToLogin::COOKIE_NAME_TRIGGERED)->willReturn(TRUE);

    $this->httpKernel->handle(Argument::any(), Argument::any(), Argument::any())->shouldBeCalled();

    $response = $this->redirectToLogin->handle($this->request->reveal());

    $this->assertNotInstanceOf(RedirectResponse::class, $response);
  }

  /**
   * @test
   */
  public function shouldRedirectWhenTheRequestIsMasterRequestAndLoginCookieExistsButLoginHasNotBeenTriggered() {
    $this->cookies->has(RedirectToLogin::COOKIE_NAME_LOGGED)->willReturn(TRUE);
    $this->cookies->has(RedirectToLogin::COOKIE_NAME_TRIGGERED)->willReturn(FALSE);

    $this->httpKernel->handle(Argument::any(), Argument::any(), Argument::any())->shouldNotBeCalled();

    $response = $this->redirectToLogin->handle($this->request->reveal());

    $this->assertInstanceOf(RedirectResponse::class, $response);
  }

  /**
   * @test
   */
  public function shouldSetLoginTriggeredCookieOnRedirect() {
    $this->cookies->has(RedirectToLogin::COOKIE_NAME_LOGGED)->willReturn(TRUE);
    $this->cookies->has(RedirectToLogin::COOKIE_NAME_TRIGGERED)->willReturn(FALSE);

    $response = $this->redirectToLogin->handle($this->request->reveal());

    /** @var \Symfony\Component\HttpFoundation\Cookie[] $cookies */
    $cookies = $response->headers->getCookies();

    $this->assertEquals(RedirectToLogin::COOKIE_NAME_TRIGGERED, $cookies[0]->getName());
  }

}
