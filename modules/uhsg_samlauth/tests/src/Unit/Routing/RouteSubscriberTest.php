<?php

use Drupal\Tests\UnitTestCase;
use Drupal\uhsg_samlauth\Routing\RouteSubscriber;
use Symfony\Component\Routing\RouteCollection;

/**
 * @group uhsg
 */
class SamlRouteSubscriberTest extends UnitTestCase {

  /** @var \Symfony\Component\Routing\RouteCollection*/
  private $routeCollection;

  /** @var \Drupal\uhsg_samlauth\Routing\RouteSubscriber*/
  private $routeSubscriber;

  public function setUp() {
    parent::setUp();

    $this->routeCollection = $this->prophesize(RouteCollection::class);
    $this->routeSubscriber = new RouteSubscriber();
  }

  /**
   * @test
   */
  public function alterRoutesShouldReplaceSamlLoginLogoutAcsSls() {
    $this->routeCollection->get('samlauth.saml_controller_login')->shouldBeCalled();
    $this->routeCollection->get('samlauth.saml_controller_logout')->shouldBeCalled();
    $this->routeCollection->get('samlauth.saml_controller_acs')->shouldBeCalled();
    $this->routeCollection->get('samlauth.saml_controller_sls')->shouldBeCalled();

    $this->routeSubscriber->alterRoutes($this->routeCollection->reveal());
  }

}
