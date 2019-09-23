<?php

use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Path\PathMatcherInterface;
use Drupal\Core\Routing\UrlGeneratorInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Tests\UnitTestCase;
use Drupal\uhsg_obar\Jwt;

/**
 * @group uhsg
 */
class JwtTest extends UnitTestCase {

  /** @var AccountInterface */
  private $account;

  /** @var ConfigFactory */
  private $configFactory;

  /** @var JWT */
  private $jwt;

  /** @var LanguageInterface */
  private $language;

  /** @var LanguageManagerInterface */
  private $languageManager;

  /** @var PathMatcherInterface */
  private $pathMatcher;

  /** @var UrlGeneratorInterface */
  private $urlGenerator;


  public function setUp() {
    parent::setUp();

    $this->account = $this->prophesize(AccountInterface::class);
    $this->configFactory = $this->prophesize(ConfigFactory::class);
    $this->language = $this->prophesize(LanguageInterface::class);

    $this->languageManager = $this->prophesize(LanguageManagerInterface::class);
    $this->languageManager->getCurrentLanguage()->willReturn($this->language);

    $this->pathMatcher = $this->prophesize(PathMatcherInterface::class);
    $this->urlGenerator = $this->prophesize(UrlGeneratorInterface::class);

    $this->jwt = new Jwt(
      $this->configFactory->reveal(),
      $this->account->reveal(),
      $this->urlGenerator->reveal(),
      $this->languageManager->reveal(),
      $this->pathMatcher->reveal()
    );
  }

  /**
   * @test
   */
  public function shouldThrowExceptionOnMissingPrivateKeyPathConfig() {
    $this->setExpectedException(Exception::class);
    $this->jwt->generateToken();
  }
}
