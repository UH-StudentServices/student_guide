<?php

use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Language\LanguageManager;
use Drupal\Tests\UnitTestCase;
use Drupal\uhsg_active_degree_programme\ActiveDegreeProgrammeService;
use Drupal\uhsg_eduviewer\Eduviewer;

/**
 * @group uhsg
 */
class EduviewerTest extends UnitTestCase {

  const ACTIVE_DEGREE_PROGRAMME_CODE = 'CODE_123';
  const LANGUAGE = 'fi';

  /** @var ActiveDegreeProgrammeService */
  private $activeDegreeProgrammeService;

  /** @var Eduviewer */
  private $eduviewer;

  /** @var LanguageInterface */
  private $language;

  /** @var LanguageManager */
  private $languageManager;

  public function setUp() {
    parent::setUp();

    $this->activeDegreeProgrammeService = $this->prophesize(ActiveDegreeProgrammeService::class);
    $this->activeDegreeProgrammeService->getCode()->willReturn(self::ACTIVE_DEGREE_PROGRAMME_CODE);

    $this->language = $this->prophesize(LanguageInterface::class);
    $this->language->getId()->willReturn(self::LANGUAGE);

    $this->languageManager = $this->prophesize(LanguageManager::class);
    $this->languageManager->getCurrentLanguage()->willReturn($this->language);

    $this->eduviewer = new Eduviewer(
      $this->activeDegreeProgrammeService->reveal(),
      $this->languageManager->reveal()
    );
  }

  /**
   * @test
   */
  public function getMarkupShouldReturnNullWhenThereIsNoActiveDegreeProgrammeCode() {
    $this->activeDegreeProgrammeService->getCode()->willReturn(NULL);

    $this->assertNull($this->eduviewer->getMarkup());
  }

  /**
   * @test
   */
  public function getMarkupShouldReturnNullWhenTheActiveDegreeProgrammeCodeIsNotValid() {
    foreach (Eduviewer::INVALID_DEGREE_PROGRAMME_CODES as $invalidDegreeProgrammeCode) {
      $this->activeDegreeProgrammeService->getCode()->willReturn($invalidDegreeProgrammeCode);
      $this->assertNull($this->eduviewer->getMarkup());
    }
  }

  /**
   * @test
   */
  public function getMarkupShouldReturnMarkupUsingActiveDegreeProgrammeCodeAndLanguage() {
    $expectedDegreeProgrammeCode = self::ACTIVE_DEGREE_PROGRAMME_CODE;
    $expectedLanguage = self::LANGUAGE;
    $expectedMarkup = "<div id=\"eduviewer-root\" degree-program-id=\"$expectedDegreeProgrammeCode\" lang=\"$expectedLanguage\"></div>";

    $this->assertEquals($expectedMarkup, $this->eduviewer->getMarkup());
  }
}
