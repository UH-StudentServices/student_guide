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
  	$entities = entity_load_multiple('some_links');
  	$some_links = array();
    /*
  	foreach ($entities as $entity) {
  		$some_links[] = array(
  			'icon' => $entity->icon_class->getValue(),
  			'url' => $entity->url->getValue()
  		);
  	} */
    return array(
        '#theme' => 'some_links',
        //'some_links' => $some_links,
        'entities' => $entities
    );
 	}
}
