<?php

use Drupal\uhsg_active_degree_programme\ActiveDegreeProgrammeService;
use Drupal\uhsg_active_degree_programme\PageCache\DenyActiveDegreeProgramme;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @group uhsg
 */
class DenyActiveDegreeProgrammeTest extends PHPUnit_Framework_TestCase {

  const ACTIVE_DEGREE_PROGRAMME = 123;

  /** @var ActiveDegreeProgrammeService */
  private $activeDegreeProgrammeService;

  /** @var DenyActiveDegreeProgramme */
  private $denyActiveDegreeProgramme;

  /** @var Request */
  private $request;

  /** @var Response */
  private $response;

  public function setUp() {
    parent::setUp();

    $this->activeDegreeProgrammeService = $this->prophesize(ActiveDegreeProgrammeService::class);
    $this->request = $this->prophesize(Request::class);
    $this->response = $this->prophesize(Response::class);

    $this->denyActiveDegreeProgramme = new DenyActiveDegreeProgramme($this->activeDegreeProgrammeService->reveal());
  }

  /**
   * @test
   */
  public function shouldDenyPageCachingWhenActiveDegreeProgrammeExists() {
    $this->activeDegreeProgrammeService->getId()->willReturn(self::ACTIVE_DEGREE_PROGRAMME);

    $result = $this->denyActiveDegreeProgramme->check($this->response->reveal(), $this->request->reveal());

    $this->assertEquals(DenyActiveDegreeProgramme::DENY, $result);
  }

  /**
   * @test
   */
  public function shouldNotDenyPageCachingWhenActiveDegreeProgrammeDoesNotExist() {
    $this->activeDegreeProgrammeService->getId()->willReturn();

    $result = $this->denyActiveDegreeProgramme->check($this->response->reveal(), $this->request->reveal());

    $this->assertNull($result);
  }
}