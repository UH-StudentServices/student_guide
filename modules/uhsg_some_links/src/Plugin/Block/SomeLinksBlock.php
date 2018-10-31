<?php

namespace Drupal\uhsg_some_links\Plugin\Block;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Provides a 'SomeLinksBlock' block.
 *
 * @Block(
 *  id = "some_links_block",
 *  admin_label = @Translation("Some links block"),
 * )
 */
class SomeLinksBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  protected function blockAccess(AccountInterface $account) {
    return AccessResult::allowedIfHasPermission($account, 'view published some links entities');
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    return [
      'entities' => $this->getEntities(),
    ];
  }

  /**
   * Get all entities that use has access to view.
   */
  protected function getEntities() {
    $entities = array_filter(
      \Drupal::entityTypeManager()->getStorage('some_links')->loadMultiple(),
      function (EntityInterface $entity) {
        return $entity->access('view');
      }
    );
    return $entities ?: [];
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    $cache_tags = ['some_links_list'];
    foreach ($this->getEntities() as $entity) {
      /** @var $entity SomeLinks */
      foreach ($entity->getCacheTags() as $entity_cache_tags) {
        $cache_tags[] = $entity_cache_tags;
      }
    }
    return Cache::mergeTags(parent::getCacheTags(), $cache_tags);
  }

}
