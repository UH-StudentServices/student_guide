<?php

namespace Drupal\uhsg_user_sync\SamlAuth;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\flag\FlagServiceInterface;
use Drupal\uhsg_oprek\Oprek\OprekServiceInterface;

class UserSyncSubscriberFactory {
  public static function create(ConfigFactoryInterface $configFactory, OprekServiceInterface $oprekService, FlagServiceInterface $flagService, EntityTypeManagerInterface $entityTypeManager) {
    return new UserSyncSubscriber($configFactory, $oprekService, $flagService, $entityTypeManager);
  }
}
