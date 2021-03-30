<?php

namespace Drupal\uhsg_user_sync\SamlAuth;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannel;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\flag\FlagServiceInterface;
use Drupal\uhsg_sisu\Services\StudyRightsService;

/**
 * Create an instance for UserSyncSubscriber.
 */
class UserSyncSubscriberFactory {

  public static function create(
    ConfigFactoryInterface $configFactory,
    StudyRightsService $studyRightsService,
    FlagServiceInterface $flagService,
    EntityTypeManagerInterface $entityTypeManager,
    LoggerChannel $logger,
    MessengerInterface $messenger
  ) {
    return new UserSyncSubscriber($configFactory, $studyRightsService, $flagService, $entityTypeManager, $logger, $messenger);
  }

}
