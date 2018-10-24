<?php

use Drupal\Core\Menu\StaticMenuLinkOverridesInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Tests\UnitTestCase;
use Drupal\uhsg_samlauth\Plugin\Menu\LoginLogoutMenuLink;

/**
 * @group uhsg
 */
class LoginLogoutMenuLinkTest extends UnitTestCase {

  const LOGIN = 'Login';
  const LOGOUT = 'Logout';

  /** @var \Drupal\Core\Session\AccountInterface*/
  private $currentUser;

  /** @var \Drupal\uhsg_samlauth\Plugin\Menu\LoginLogoutMenuLink*/
  private $loginLogoutMenuLink;

  /** @var \Drupal\Core\Menu\StaticMenuLinkOverridesInterface*/
  private $staticMenuLinkOverrides;

  public function setUp() {
    parent::setUp();

    $this->currentUser = $this->prophesize(AccountInterface::class);
    $this->staticMenuLinkOverrides = $this->prophesize(StaticMenuLinkOverridesInterface::class);

    $this->loginLogoutMenuLink = new LoginLogoutMenuLinkTestDouble([], NULL, NULL, $this->staticMenuLinkOverrides->reveal(), $this->currentUser->reveal());
  }

  /**
   * @test
   */
  public function getTitleShouldReturnLoginLinkForAnonymousUser() {
    $this->currentUser->isAuthenticated()->willReturn(FALSE);

    $this->assertEquals(self::LOGIN, $this->loginLogoutMenuLink->getTitle());
  }

  /**
   * @test
   */
  public function getTitleShouldReturnLogoutLinkForAuthenticatedUser() {
    $this->currentUser->isAuthenticated()->willReturn(TRUE);

    $this->assertEquals(self::LOGOUT, $this->loginLogoutMenuLink->getTitle());
  }

}

/**
 * Test double for overriding difficult to test methods.
 */
class LoginLogoutMenuLinkTestDouble extends LoginLogoutMenuLink {

  public function t($string, array $args = [], array $options = []) {
    return $string;
  }

}
