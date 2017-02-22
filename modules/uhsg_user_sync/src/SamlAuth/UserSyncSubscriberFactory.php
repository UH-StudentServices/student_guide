<?php

namespace Drupal\uhsg_user_sync\SamlAuth;

use Drupal\Core\Config\ConfigFactory;
use Drupal\uhsg_oprek\Oprek\OprekServiceInterface;

class UserSyncSubscriberFactory {
  public static function create(ConfigFactory $configFactory, OprekServiceInterface $oprekService) {
    return new UserSyncSubscriber($configFactory, $oprekService);
  }
}
