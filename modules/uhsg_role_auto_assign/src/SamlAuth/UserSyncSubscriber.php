<?php

namespace Drupal\uhsg_role_auto_assign\SamlAuth;

use Drupal\Core\Config\ImmutableConfig;
use Drupal\samlauth\Event\SamlAuthEvents;
use Drupal\samlauth\Event\SamlAuthUserSyncEvent;
use Drupal\uhsg_samlauth\AttributeParser;
use Drupal\uhsg_samlauth\AttributeParserInterface;
use Drupal\user\Entity\Role;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class UserSyncSubscriber implements EventSubscriberInterface {

  /**
   * @var ImmutableConfig
   */
  protected $config;

  /**
   * @var LoggerInterface
   */
  protected $logger;

  public function __construct() {
    $this->config = \Drupal::configFactory()->get('uhsg_role_auto_assign.settings');
    $this->logger = \Drupal::logger('uhsg_role_auto_assign');
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[SamlAuthEvents::USER_SYNC][] = ['onUserSync'];
    return $events;
  }

  /**
   * During user synchronization add/remove roles to/from the account according
   * to auto assignable roles.
   *
   * @param SamlAuthUserSyncEvent $event
   */
  public function onUserSync(SamlAuthUserSyncEvent $event) {
    $account = $event->getAccount();
    $attributes = new AttributeParser($event->getAttributes());
    foreach ($this->getAutoAssignableRoles() as $group => $role) {
      /** @var Role $role */
      if (!$account->hasRole($role->id()) && $this->hasGroupInAttributes($group, $attributes)) {
        $account->addRole($role->id());
        $event->markAccountChanged();
        $this->logger->debug('Assigned role @role for user @user', array('@role' => $role->label(), $account->label()));
      }
      elseif ($account->hasRole($role->id()) && !$this->hasGroupInAttributes($group, $attributes)) {
        $account->removeRole($role->id());
        $event->markAccountChanged();
        $this->logger->debug('Unassigned role @role from user @user', array('@role' => $role->label(), $account->label()));
      }
    }
  }

  /**
   * Gets auto assignable roles defined by the configuration and that exists in
   * the system.
   * @return array
   */
  protected function getAutoAssignableRoles() {
    $auto_assignable_roles = [];
    if ($this->config->get('group_to_roles')) {
      foreach ($this->config->get('group_to_roles') as $item) {
        if ($role = Role::load($item['rid'])) {
          $auto_assignable_roles[$item['group_name']] = $role;
        }
      }
    }
    return $auto_assignable_roles;
  }

  /**
   * Checks whether group exists in given attributes.
   * @param $group
   * @param AttributeParserInterface $attributes
   * @return bool
   */
  protected function hasGroupInAttributes($group, AttributeParserInterface $attributes) {
    foreach ($attributes->getGroups() as $attribute_group) {
      if ($group == $attribute_group) {
        return TRUE;
      }
    }
    return FALSE;
  }

}
