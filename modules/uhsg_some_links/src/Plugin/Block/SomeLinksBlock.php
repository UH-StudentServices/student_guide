<?php

namespace Drupal\uhsg_some_links\Plugin\Block;

use Drupal\Core\Block\BlockBase;

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
      'entities' => entity_load_multiple('some_links')
    );
 	}
}
