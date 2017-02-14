<?php

namespace Drupal\uhsg_role_auto_assign\SamlAuth;

use Drupal\samlauth\Event\SamlAuthEvents;
use Drupal\samlauth\Event\SamlAuthUserSyncEvent;
use Drupal\uhsg_samlauth\AttributeParser;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class UserSyncSubscriber implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[SamlAuthEvents::USER_SYNC][] = ['onUserSync'];
    return $events;
  }

  /**
   * React on user sync event.
   *
   * @param SamlAuthUserSyncEvent $event
   */
  public function onUserSync(SamlAuthUserSyncEvent $event) {
    // TODO: For now, just logging
    $attributes = new AttributeParser($event->getAttributes());
    $values = [
      'cn' => $attributes->getCommonName(),
      'email' => $attributes->getEmailAddress(),
      'logout' => $attributes->getLogoutUrl(),
      'ouid' => $attributes->getOodiUid(),
      'studentid' => $attributes->getStudentID(),
      'uid' => $attributes->getUserId(),
    ];
    $logger = \Drupal::logger('uhsg_role_auto_assign');
    $logger->debug('User sync triggered: @attributes', array('@attributes' => print_r($values,1)));
  }

}
