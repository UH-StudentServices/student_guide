<?php

use Drupal\Core\Entity\EntityRepositoryInterface;
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

  /** @var \Drupal\Core\Routing\CurrentRouteMatch*/
  private $currentRouteMatch;

  /** @var \Drupal\Core\Entity\EntityRepositoryInterface*/
  private $entityRepository;

  /** @var \Drupal\Core\Language\LanguageInterface*/
  private $language;

  /** @var \Drupal\Core\Language\LanguageManagerInterface*/
  private $languageManager;

  /** @var \Drupal\node\Entity\Node*/
  private $node;

  /** @var \Drupal\uhsg_themes\Plugin\Block\ThemesReferencingInstructions*/
  private $themesReferencingInstructions;

  public function setUp() {
    parent::setUp();

    $this->node = $this->prophesize(Node::class);
    $this->node->hasTranslation(self::LANGUAGE_ID)->willReturn(TRUE);

    $this->currentRouteMatch = $this->prophesize(CurrentRouteMatch::class);
    $this->currentRouteMatch->getParameter('node')->willReturn($this->node);

    $this->entityRepository = $this->prophesize(EntityRepositoryInterface::class);

    $this->language = $this->prophesize(LanguageInterface::class);
    $this->language->getId()->willReturn(self::LANGUAGE_ID);

    $this->languageManager = $this->prophesize(LanguageManagerInterface::class);
    $this->languageManager->getCurrentLanguage()->willReturn($this->language);

    $this->themesReferencingInstructions = new ThemesReferencingInstructions(
      [], NULL, NULL, $this->entityRepository->reveal(), $this->languageManager->reveal(), $this->currentRouteMatch->reveal()
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
