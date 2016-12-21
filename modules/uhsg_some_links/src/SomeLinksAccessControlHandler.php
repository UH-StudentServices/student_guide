<?php

namespace Drupal\uhsg_some_links;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the Some links entity.
 *
 * @see \Drupal\uhsg_some_links\Entity\SomeLinks.
 */
class SomeLinksAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\uhsg_some_links\Entity\SomeLinksInterface $entity */
    switch ($operation) {
      case 'view':
        if (!$entity->isPublished()) {
          return AccessResult::allowedIfHasPermission($account, 'view unpublished some links entities');
        }
        return AccessResult::allowedIfHasPermission($account, 'view published some links entities');

      case 'update':
        return AccessResult::allowedIfHasPermission($account, 'edit some links entities');

      case 'delete':
        return AccessResult::allowedIfHasPermission($account, 'delete some links entities');
    }

    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add some links entities');
  }

}
