<?php

use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Tests\UnitTestCase;
use Drupal\uhsg_top_content\Plugin\views\argument_default\ActiveLanguage;

/**
 * @group uhsg
 */
class ActiveLanguageTest extends UnitTestCase {

  const LANGUAGE_ID = 'fi';

  /** @var \Drupal\uhsg_top_content\Plugin\views\argument_default\ActiveLanguage*/
  private $activeLanguage;

  /** @var \Drupal\Core\Language\LanguageInterface*/
  private $language;

  /** @var \Drupal\Core\Language\LanguageManagerInterface*/
  private $languageManager;

  public function setUp() {
    $this->language = $this->prophesize(LanguageInterface::class);
    $this->language->getId()->willReturn(self::LANGUAGE_ID);

    $this->languageManager = $this->prophesize(LanguageManagerInterface::class);
    $this->languageManager->getCurrentLanguage()->willReturn($this->language);

    $this->activeLanguage = new ActiveLanguage([], NULL, [], $this->languageManager->reveal());
  }

  /**
   * @test
   */
  public function shouldNotGetCached() {
    $this->assertEquals(0, $this->activeLanguage->getCacheMaxAge());
  }

  /**
   * @test
   */
  public function shouldHaveInterfaceLanguageAsCacheContext() {
    $this->assertEquals(['languages:language_interface'], $this->activeLanguage->getCacheContexts());
  }

  /**
   * @test
   */
  public function shouldReturnCurrentLanguageIdAsDefaultArgument() {
    $this->assertEquals(self::LANGUAGE_ID, $this->activeLanguage->getArgument());
  }

}
