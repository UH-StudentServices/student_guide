<?php

use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Tests\UnitTestCase;
use Drupal\uhsg_top_content\Plugin\views\argument_default\ActiveLanguage;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @group uhsg
 */
class ActiveLanguageTest extends UnitTestCase {

  const LANGUAGE_ID = 'fi';

  /** @var ActiveLanguage */
  private $activeLanguage;

  /** @var ContainerInterface */
  private $container;

  /** @var LanguageInterface */
  private $language;

  /** @var LanguageManagerInterface */
  private $languageManager;

  public function setUp() {
    $this->language = $this->prophesize(LanguageInterface::class);
    $this->language->getId()->willReturn(self::LANGUAGE_ID);

    $this->languageManager = $this->prophesize(LanguageManagerInterface::class);
    $this->languageManager->getCurrentLanguage()->willReturn($this->language);

    $this->container = $this->prophesize(ContainerInterface::class);
    $this->container->get('language_manager')->willReturn($this->languageManager);

    Drupal::setContainer($this->container->reveal());

    $this->activeLanguage = new ActiveLanguage([], NULL, []);
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
