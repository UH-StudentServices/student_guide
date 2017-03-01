<?php

namespace Drupal\edit_own_user_account_permission\Access;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Routing\Access\AccessInterface;
use Drupal\Core\Session\AccountInterface;

class EditOwnUserAccountAccessCheck implements AccessInterface {

  /**
   * Checks access for editing own user account while current user being the
   * target account.
   *
   * @param AccountInterface $user
   *   The user account that is to be edited.
   * @param AccountInterface $account
   *   User account that is from the current session.
   * @return AccessResult
   */
  public function access(AccountInterface $user, AccountInterface $account) {
    $can_edit_own_account = ($account->hasPermission('edit own user account') && $user->id() == $account->id());
    return AccessResult::allowedIf($can_edit_own_account || $account->hasPermission('administer users'));
  }

}
