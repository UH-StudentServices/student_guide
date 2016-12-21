<?php

namespace Drupal\uhsg_some_links\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Entity\EntityInterface;

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
  public function build() {
    return array(
      'entities' => $this->getEntities(),
    );
  }

  /**
   * Get all entities that use has access to view.
   */
  protected function getEntities() {
    $entities = array_filter(
      \Drupal::entityTypeManager()->getStorage('some_links')->loadMultiple(),
      function(EntityInterface $entity){
        return $entity->access('view');
      }
    );
    return $entities ?: [];
  }
}
