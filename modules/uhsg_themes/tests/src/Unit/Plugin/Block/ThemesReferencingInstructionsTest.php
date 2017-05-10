<?php

use Drupal\Core\Routing\CurrentRouteMatch;
use Drupal\node\Entity\Node;
use Drupal\Tests\UnitTestCase;
use Drupal\uhsg_themes\Plugin\Block\ThemesReferencingInstructions;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @group uhsg
 */
class ThemesReferencingInstructionsTest extends UnitTestCase {

  /** @var ContainerInterface */
  private $container;

  /** @var CurrentRouteMatch */
  private $currentRouteMatch;

  /** @var Node */
  private $node;

  /** @var ThemesReferencingInstructions */
  private $themesReferencingInstructions;

  public function setUp() {
    parent::setUp();

    $this->node = $this->prophesize(Node::class);

    $this->currentRouteMatch = $this->prophesize(CurrentRouteMatch::class);
    $this->currentRouteMatch->getParameter('node')->willReturn($this->node);

    $this->container = $this->prophesize(ContainerInterface::class);
    $this->container->get('current_route_match')->willReturn($this->currentRouteMatch);

    Drupal::setContainer($this->container->reveal());

    $this->themesReferencingInstructions = new ThemesReferencingInstructionsTestDouble();
  }

  /**
   * @test
   */
  public function buildShouldReturnEmptyRenderArrayWhereNodeParameterDoesNotExist() {
    $this->currentRouteMatch->getParameter('node')->willReturn(NULL);

    $this->assertEmpty($this->themesReferencingInstructions->build());
  }
}

/**
 * Test double for overriding difficult to test methods.
 */
class ThemesReferencingInstructionsTestDouble extends ThemesReferencingInstructions {

  public function __construct(array $configuration = [], $plugin_id = NULL, $plugin_definition = NULL) {
    // Do nothing.
  }
}