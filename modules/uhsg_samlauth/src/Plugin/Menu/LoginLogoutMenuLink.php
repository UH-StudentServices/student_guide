<?php

namespace Drupal\uhsg_samlauth\Plugin\Menu;

use Drupal\samlauth\Plugin\Menu\LoginLogoutMenuLink as DrupalLoginLogoutMenuLink;

/**
 * A menu link that shows "Login" or "Logout" as appropriate.
 */
class LoginLogoutMenuLink extends DrupalLoginLogoutMenuLink {

  /**
   * {@inheritdoc}
   */
  public function getTitle() {
    if ($this->currentUser->isAuthenticated()) {
      return $this->t('Logout');
    }
    else {
      return $this->t('Login');
    }
  }
}
