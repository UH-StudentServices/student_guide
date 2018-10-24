<?php

use Drupal\Tests\UnitTestCase;
use Drupal\uhsg_samlauth\Controller\SamlController;
use Drupal\uhsg_samlauth\SamlService;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * @group uhsg
 */
class SamlControllerTest extends UnitTestCase {

  /** @var \Drupal\uhsg_samlauth\Controller\SamlController*/
  private $samlController;

  /** @var \Drupal\uhsg_samlauth\SamlService*/
  private $samlService;

  public function setUp() {
    parent::setUp();

    $this->samlService = $this->prophesize(SamlService::class);

    $this->samlController = new SamlController($this->samlService->reveal());
  }

  /**
   * @test
   */
  public function acsShouldReturnRootRedirectResponseOnSamlServiceException() {
    $this->samlService->acs()->willThrow(new Exception());

    $this->assertEquals(new RedirectResponse('/'), $this->samlController->acs());
  }

}
