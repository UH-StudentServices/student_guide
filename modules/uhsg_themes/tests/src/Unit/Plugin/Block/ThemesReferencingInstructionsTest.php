<?php

use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Routing\CurrentRouteMatch;
use Drupal\node\Entity\Node;
use Drupal\Tests\UnitTestCase;
use Drupal\uhsg_themes\Plugin\Block\ThemesReferencingInstructions;

/**
 * @group uhsg
 */
class ThemesReferencingInstructionsTest extends UnitTestCase {

  /** @var CurrentRouteMatch */
  private $currentRouteMatch;

  /** @var EntityManagerInterface */
  private $entityManager;

  /** @var Node */
  private $node;

  /** @var ThemesReferencingInstructions */
  private $themesReferencingInstructions;

  public function setUp() {
    parent::setUp();

    $this->node = $this->prophesize(Node::class);

    $this->currentRouteMatch = $this->prophesize(CurrentRouteMatch::class);
    $this->currentRouteMatch->getParameter('node')->willReturn($this->node);

    $this->entityManager = $this->prophesize(EntityManagerInterface::class);

    $this->themesReferencingInstructions = new ThemesReferencingInstructions([], NULL, NULL, $this->entityManager->reveal(), $this->currentRouteMatch->reveal());
  }

  /**
   * @test
   */
  public function buildShouldReturnEmptyRenderArrayWhereNodeParameterDoesNotExist() {
    $this->currentRouteMatch->getParameter('node')->willReturn(NULL);

    $this->assertEmpty($this->themesReferencingInstructions->build());
  }
}
