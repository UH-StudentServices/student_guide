<?php

use Drupal\Tests\UnitTestCase;
use Drupal\uhsg_top_content\Plugin\views\argument_default\ActiveLanguage;

/**
 * @group uhsg
 */
class ActiveLanguageTest extends UnitTestCase {

  /** @var ActiveLanguage */
  private $activeLanguage;

  public function setUp() {
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
}
