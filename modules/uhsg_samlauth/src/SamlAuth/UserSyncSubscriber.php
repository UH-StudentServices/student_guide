<?php

namespace Drupal\uhsg_samlauth\SamlAuth;

use Drupal\samlauth\Event\SamlAuthEvents;
use Drupal\samlauth\Event\SamlAuthUserSyncEvent;
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
    \Drupal::logger('uhsg_samlauth')->debug('User sync triggered: @attributes', array('@attributes' => print_r($event->getAttributes(),1)));
  }

}
