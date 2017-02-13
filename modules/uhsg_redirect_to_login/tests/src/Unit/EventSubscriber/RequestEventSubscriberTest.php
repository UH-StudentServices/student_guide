<?php

use Drupal\Tests\UnitTestCase;
use Drupal\uhsg_redirect_to_login\EventSubscriber\RequestEventSubscriber;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * @group uhsg
 */
class RequestEventSubscriberTest extends UnitTestCase {

  /** @var RequestEventSubscriber */
  private $requestEventSubscriber;

  public function setUp() {
    parent::setUp();

    $this->requestEventSubscriber = new RequestEventSubscriber();
  }

  /**
   * @test
   */
  public function shouldSubscribeToOnRequestEvent() {
    $events = $this->requestEventSubscriber->getSubscribedEvents();

    $this->assertEquals(['onRequest'], $events[KernelEvents::REQUEST][0]);
  }
}
