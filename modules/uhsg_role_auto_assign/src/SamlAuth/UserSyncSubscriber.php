<?php

namespace Drupal\uhsg_role_auto_assign\SamlAuth;

use Drupal\samlauth\Event\SamlAuthEvents;
use Drupal\samlauth\Event\SamlAuthUserSyncEvent;
use Drupal\uhsg_samlauth\AttributeParser;
use Drupal\uhsg_samlauth\AttributeParserInterface;
use Drupal\user\Entity\Role;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class UserSyncSubscriber implements EventSubscriberInterface {

  /**
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected $config;

  /**
   * @var \Psr\Log\LoggerInterface
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
   * @param \Drupal\samlauth\Event\SamlAuthUserSyncEvent $event
   */
  public function onUserSync(SamlAuthUserSyncEvent $event) {
    $account = $event->getAccount();
    $attributes = new AttributeParser($event->getAttributes());
    foreach ($this->getAutoAssignableRoles() as $rid => $groups) {
      if (!$account->hasRole($rid) && $this->hasGroupsInAttributes($groups, $attributes)) {
        $account->addRole($rid);
        $event->markAccountChanged();
        $this->logger->debug('Assigned role @role for user @user', ['@role' => $rid, $account->label()]);
      }
      elseif ($account->hasRole($rid) && !$this->hasGroupsInAttributes($groups, $attributes)) {
        $account->removeRole($rid);
        $event->markAccountChanged();
        $this->logger->debug('Unassigned role @role from user @user', ['@role' => $rid, $account->label()]);
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
          $auto_assignable_roles[$role->id()][] = $item['group_name'];
        }
      }
    }
    return $auto_assignable_roles;
  }

  /**
   * Checks whether one of the groups exists in given attributes.
   * @param array $groups
   * @param \Drupal\uhsg_samlauth\AttributeParserInterface $attributes
   * @return bool
   */
  protected function hasGroupsInAttributes(array $groups, AttributeParserInterface $attributes) {
    foreach ($attributes->getGroups() as $attribute_group) {
      if (in_array($attribute_group, $groups)) {
        return TRUE;
      }
    }
    return FALSE;
  }

}
