<?php

use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Routing\CurrentRouteMatch;
use Drupal\node\Entity\Node;
use Drupal\Tests\UnitTestCase;
use Drupal\uhsg_themes\Plugin\Block\ThemesReferencingInstructions;

/**
 * @group uhsg
 */
class ThemesReferencingInstructionsTest extends UnitTestCase {

  const LANGUAGE_ID = 'fi';

  /** @var CurrentRouteMatch */
  private $currentRouteMatch;

  /** @var EntityManagerInterface */
  private $entityManager;

  /** @var LanguageInterface */
  private $language;

  /** @var LanguageManagerInterface */
  private $languageManager;

  /** @var Node */
  private $node;

  /** @var ThemesReferencingInstructions */
  private $themesReferencingInstructions;

  public function setUp() {
    parent::setUp();

    $this->node = $this->prophesize(Node::class);
    $this->node->hasTranslation(self::LANGUAGE_ID)->willReturn(TRUE);

    $this->currentRouteMatch = $this->prophesize(CurrentRouteMatch::class);
    $this->currentRouteMatch->getParameter('node')->willReturn($this->node);

    $this->entityManager = $this->prophesize(EntityManagerInterface::class);

    $this->language = $this->prophesize(LanguageInterface::class);
    $this->language->getId()->willReturn(self::LANGUAGE_ID);

    $this->languageManager = $this->prophesize(LanguageManagerInterface::class);
    $this->languageManager->getCurrentLanguage()->willReturn($this->language);

    $this->themesReferencingInstructions = new ThemesReferencingInstructions(
      [], NULL, NULL, $this->entityManager->reveal(), $this->languageManager->reveal(), $this->currentRouteMatch->reveal()
    );
  }

  /**
   * @test
   */
  public function buildShouldReturnEmptyRenderArrayWhereNodeParameterDoesNotExist() {
    $this->currentRouteMatch->getParameter('node')->willReturn(NULL);

    $this->assertEmpty($this->themesReferencingInstructions->build());
  }
}
