<?php

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Tests\UnitTestCase;
use Drupal\uhsg_oprek\Oprek\OprekService;
use GuzzleHttp\Client;

/**
 * @group uhsg
 */
class OprekServiceTest extends UnitTestCase {

  /** @var Client */
  private $client;

  /** @var ConfigFactoryInterface */
  private $configFactory;

  /** @var OprekService */
  private $oprekService;

  public function setUp() {
    parent::setUp();

    $this->client = $this->prophesize(Client::class);
    $this->configFactory = $this->prophesize(ConfigFactoryInterface::class);

    $this->oprekService = new OprekService($this->configFactory->reveal(), $this->client->reveal());
  }

  /**
   * @test
   */
  public function getStudyRightsShouldThrowExceptionWhenTheStudentNumberIsNotString() {
    $this->setExpectedException(\InvalidArgumentException::class);

    $this->oprekService->getStudyRights(NULL);
  }
}