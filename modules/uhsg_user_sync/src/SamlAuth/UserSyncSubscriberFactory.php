<?php

namespace Drupal\uhsg_user_sync\SamlAuth;

use Symfony\Component\DependencyInjection\ContainerInterface;

class UserSyncSubscriberFactory {
  public static function create(ContainerInterface $container) {
    return new UserSyncSubscriber(
      $container->get('config.factory'),
      $container->get('uhsg_oprek.oprek_service'),
      $container->get('flag'),
      $container->get('entity.manager')
    );
  }
}
