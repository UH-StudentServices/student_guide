<?php

use Drupal\Core\Access\AccessResultAllowed;
use Drupal\Core\Access\AccessResultNeutral;
use Drupal\Core\Session\AccountInterface;
use Drupal\edit_own_user_account_permission\Access\EditOwnUserAccountAccessCheck;

/**
 * @group uhsg
 */
class EditOwnUserAccountAccessCheckTest extends PHPUnit_Framework_TestCase {

  const ADMINISTER_USERS = 'administer users';
  const EDIT_OWN_USER_ACCOUNT = 'edit own user account';
  const ID_1 = 1;
  const ID_2 = 2;

  /** @var AccountInterface */
  private $currentAccount;

  /** @var EditOwnUserAccountAccessCheck */
  private $editOwnUserAccountAccessCheck;

  /** @var AccountInterface */
  private $targetAccount;

  public function setUp() {
    parent::setUp();

    $this->currentAccount = $this->prophesize(AccountInterface::class);
    $this->targetAccount = $this->prophesize(AccountInterface::class);

    $this->editOwnUserAccountAccessCheck = new EditOwnUserAccountAccessCheck();
  }

  /**
   * @test
   */
  public function accessShouldBeAllowedWhenTheTargetAccountEqualsCurrentAccountAndTheCurrentAccountHasPermissionToEditOwnUserAccount() {
    $this->currentAccountEqualsTargetAccount();
    $this->currentAccount->hasPermission(self::EDIT_OWN_USER_ACCOUNT)->willReturn(TRUE);

    $accessResult = $this->editOwnUserAccountAccessCheck->access(
      $this->targetAccount->reveal(), $this->currentAccount->reveal()
    );

    $this->assertInstanceOf(AccessResultAllowed::class, $accessResult);
  }

  /**
   * @test
   */
  public function accessShouldBeAllowedWhenTheCurrentAccountHasPermissionToAdministerUsers() {
    $this->currentAccountDoesNotEqualTargetAccount();
    $this->currentAccount->hasPermission(self::EDIT_OWN_USER_ACCOUNT)->willReturn(FALSE);
    $this->currentAccount->hasPermission(self::ADMINISTER_USERS)->willReturn(TRUE);

    $accessResult = $this->editOwnUserAccountAccessCheck->access(
      $this->targetAccount->reveal(), $this->currentAccount->reveal()
    );

    $this->assertInstanceOf(AccessResultAllowed::class, $accessResult);
  }

  /**
   * @test
   */
  public function accessShouldBeNeutralWhenTheTargetAccountEqualsCurrentAccountAndTheCurrentAccountDoesNotHavePermissionToEditOwnUserAccount() {
    $this->currentAccountEqualsTargetAccount();
    $this->currentAccount->hasPermission(self::EDIT_OWN_USER_ACCOUNT)->willReturn(FALSE);
    $this->currentAccount->hasPermission(self::ADMINISTER_USERS)->willReturn(FALSE);

    $accessResult = $this->editOwnUserAccountAccessCheck->access(
      $this->targetAccount->reveal(), $this->currentAccount->reveal()
    );

    $this->assertInstanceOf(AccessResultNeutral::class, $accessResult);
  }

  /**
   * @test
   */
  public function accessShouldBeNeutralWhenTheTargetAccountDoesNotEqualCurrentAccountAndTheCurrentDoesNotHavePermissionAdministerUsers() {
    $this->currentAccountDoesNotEqualTargetAccount();
    $this->currentAccount->hasPermission(self::EDIT_OWN_USER_ACCOUNT)->willReturn(TRUE);
    $this->currentAccount->hasPermission(self::ADMINISTER_USERS)->willReturn(FALSE);

    $accessResult = $this->editOwnUserAccountAccessCheck->access(
      $this->targetAccount->reveal(), $this->currentAccount->reveal()
    );

    $this->assertInstanceOf(AccessResultNeutral::class, $accessResult);
  }

  private function currentAccountEqualsTargetAccount() {
    $this->currentAccount->id()->willReturn(self::ID_1);
    $this->targetAccount->id()->willReturn(self::ID_1);
  }

  private function currentAccountDoesNotEqualTargetAccount() {
    $this->currentAccount->id()->willReturn(self::ID_1);
    $this->targetAccount->id()->willReturn(self::ID_2);
  }
}
