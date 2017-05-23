<?php

use Drupal\Core\Path\PathValidator;
use Drupal\Core\Utility\UnroutedUrlAssembler;
use Drupal\taxonomy\Entity\Term;
use Drupal\Tests\UnitTestCase;
use Drupal\uhsg_active_degree_programme\Controller\ActiveDegreeProgrammeController;
use Drupal\uhsg_active_degree_programme\ActiveDegreeProgrammeService;
use Prophecy\Argument;
use Prophecy\Prophet;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\ServerBag;

/**
 * @group uhsg
 */
class ActiveDegreeProgrammeControllerTest extends UnitTestCase {

  const HTTP_REFERER = 'http:://www.example.com';
  const TID = 123;

  /** @var ActiveDegreeProgrammeController */
  private $activeDegreeProgrammeController;

  /** @var ActiveDegreeProgrammeService */
  private $activeDegreeProgrammeService;

  /** @var ContainerInterface */
  private $container;

  /** @var PathValidator */
  private $pathValidator;

  /** @var Request */
  private $request;

  /** @var RequestStack */
  private $requestStack;

  /** @var ServerBag */
  private $server;

  /** @var UnroutedUrlAssembler */
  private $unroutedUrlAssembler;

  public function setUp() {
    parent::setUp();

    $this->activeDegreeProgrammeService = $this->prophesize(ActiveDegreeProgrammeService::class);

    $this->pathValidator = $this->prophesize(PathValidator::class);

    $this->server = $this->prophesize(ServerBag::class);
    $this->server->get('HTTP_REFERER', 'internal:/')->willReturn(self::HTTP_REFERER);

    $this->request = $this->prophesize(Request::class);
    $this->request->server = $this->server;

    $this->requestStack = $this->prophesize(RequestStack::class);
    $this->requestStack->getCurrentRequest()->willReturn($this->request);

    $this->unroutedUrlAssembler = $this->prophesize(UnroutedUrlAssembler::class);
    $this->unroutedUrlAssembler->assemble(Argument::any(), Argument::any(), Argument::any())->willReturn(self::HTTP_REFERER);

    $this->container = $this->prophesize(ContainerInterface::class);
    $this->container->get('path.validator')->willReturn($this->pathValidator);
    $this->container->get('request_stack')->willReturn($this->requestStack);
    $this->container->get('unrouted_url_assembler')->willReturn($this->unroutedUrlAssembler);

    Drupal::setContainer($this->container->reveal());

    $this->activeDegreeProgrammeController = new ActiveDegreeProgrammeControllerTestDouble(
      $this->activeDegreeProgrammeService->reveal()
    );
  }

  /**
   * @test
   */
  public function setActiveDegreeProgrammeShouldReturnRedirectResponse() {
    $response = $this->activeDegreeProgrammeController->setActiveDegreeProgramme(self::TID);

    $this->assertInstanceOf(RedirectResponse::class, $response);
    $this->assertEquals($response->getTargetUrl(), self::HTTP_REFERER);
  }

  /**
   * @test
   */
  public function resetShouldDelegateToActiveDegreeProgrammeService() {
    $this->activeDegreeProgrammeService->reset()->shouldBeCalled();

    $this->activeDegreeProgrammeController->resetActiveDegreeProgramme();
  }
}

class ActiveDegreeProgrammeControllerTestDouble extends ActiveDegreeProgrammeController {

  public function loadTerm($tid) {
    $prophet = new Prophet();

    return $prophet->prophesize(Term::class)->reveal();
  }
}