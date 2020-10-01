<?php

namespace Drupal\uhsg_user_sync\SamlAuth;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannel;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\flag\FlagServiceInterface;
use Drupal\uhsg_oprek\Oprek\OprekServiceInterface;
use Drupal\uhsg_sisu\Services\StudentRightsService;

/**
 * Create an instance for UserSyncSubscriber.
 */
class UserSyncSubscriberFactory {

  public static function create(
    ConfigFactoryInterface $configFactory,
    OprekServiceInterface $oprekService,
    StudentRightsService $studentRightsService,
    FlagServiceInterface $flagService,
    EntityTypeManagerInterface $entityTypeManager,
    LoggerChannel $logger,
    MessengerInterface $messenger
  ) {
    return new UserSyncSubscriber($configFactory, $oprekService, $studentRightsService, $flagService, $entityTypeManager, $logger, $messenger);
  }

}
