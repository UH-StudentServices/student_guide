<?php

namespace Drupal\uhsg_user_sync\SamlAuth;

use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\flag\FlagServiceInterface;
use Drupal\uhsg_oprek\Oprek\OprekServiceInterface;

class UserSyncSubscriberFactory {
  public static function create(ConfigFactory $configFactory, OprekServiceInterface $oprekService, FlagServiceInterface $flagService, EntityManagerInterface $entityManager) {
    return new UserSyncSubscriber($configFactory, $oprekService, $flagService, $entityManager);
  }
}
