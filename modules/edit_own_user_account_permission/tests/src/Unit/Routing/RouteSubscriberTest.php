<?php

use Drupal\Core\Entity\EntityTypeManagerInterface;
use \Drupal\edit_own_user_account_permission\Routing\RouteSubscriber;
use Prophecy\Argument;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

/**
 * @group uhsg
 */
class RouteSubscriberTest extends PHPUnit_Framework_TestCase {

  /** @var EntityTypeManagerInterface */
  private $entityTypeManager;

  /** @var Route */
  private $route;

  /** @var RouteCollection */
  private $routeCollection;

  /** @var RouteSubscriber */
  private $routeSubscriber;

  public function setUp() {
    parent::setUp();
    $this->entityTypeManager = $this->prophesize(EntityTypeManagerInterface::class);
    $this->route = $this->prophesize(Route::class);

    $this->routeCollection = $this->prophesize(RouteCollection::class);
    $this->routeCollection->get('entity.user.edit_form')->willReturn($this->route);

    $this->routeSubscriber = new RouteSubscriber($this->entityTypeManager->reveal());
  }

  /**
   * @test
   */
  public function shouldSubscribeToUserEditFormRoute() {
    $this->route->setRequirement(Argument::any(), Argument::any())->shouldBeCalled();

    $this->routeSubscriber->alterRoutes($this->routeCollection->reveal());
  }

  /**
   * @test
   */
  public function shouldNotSubscribeToOtherThanUserEditFormRoute() {
    $this->routeCollection->get('entity.user.edit_form')->willReturn(NULL);
    $this->route->setRequirement(Argument::any(), Argument::any())->shouldNotBeCalled();

    $this->routeSubscriber->alterRoutes($this->routeCollection->reveal());
  }
}