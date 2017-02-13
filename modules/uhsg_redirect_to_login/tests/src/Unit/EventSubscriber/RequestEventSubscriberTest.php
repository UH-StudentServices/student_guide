<?php

use Drupal\Tests\UnitTestCase;
use Drupal\uhsg_redirect_to_login\EventSubscriber\RequestEventSubscriber;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * @group uhsg
 */
class RequestEventSubscriberTest extends UnitTestCase {

  /** @var ParameterBag */
  private $cookies;

  /** @var GetResponseEvent */
  private $event;

  /** @var Request */
  private $request;

  /** @var RequestEventSubscriber */
  private $requestEventSubscriber;

  public function setUp() {
    parent::setUp();

    $this->cookies = $this->prophesize(ParameterBag::class);

    $this->request = $this->prophesize(Request::class);
    $this->request->cookies = $this->cookies;

    $this->event = $this->prophesize(GetResponseEvent::class);
    $this->event->getRequest()->willReturn($this->request);

    $this->requestEventSubscriber = new RequestEventSubscriber();
  }

  /**
   * @test
   */
  public function shouldSubscribeToOnRequestEvent() {
    $events = $this->requestEventSubscriber->getSubscribedEvents();

    $this->assertEquals(['onRequest'], $events[KernelEvents::REQUEST][0]);
  }

  /**
   * @test
   */
  public function onRequestShouldNotRespondToEventWhenCookiesDoNotExist() {
    $this->event->setResponse()->shouldNotBeCalled();

    $this->requestEventSubscriber->onRequest($this->event->reveal());
  }
}
