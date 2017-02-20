<?php

namespace Drupal\uhsg_user_sync\SamlAuth;

use Drupal\Core\Config\ConfigFactory;

class UserSyncSubscriberFactory {
  public static function create(ConfigFactory $configFactory) {
    return new UserSyncSubscriber($configFactory);
  }
}
