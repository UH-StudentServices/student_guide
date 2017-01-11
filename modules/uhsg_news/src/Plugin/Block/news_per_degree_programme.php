<?php

namespace Drupal\uhsg_news\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Entity\EntityManagerInterface;

/**
 * Provides a 'news_per_degree_programme' block.
 *
 * @Block(
 *  id = "news_per_degree_programme",
 *  admin_label = @Translation("News per degree programme"),
 * )
 */
class news_per_degree_programme extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {

		$query = \Drupal::entityQuery('node')
	    ->condition('status', 1)
	    ->condition('langcode', 'en')
	    ->condition('type', 'news');

		$group = $query->orConditionGroup()
    	->condition('field_news_degree_programme', NULL, 'IS NULL')
    	->condition('field_news_degree_programme', 18);
	
		$nids = $query->condition($group)->execute();

		$nodes = \Drupal\node\Entity\Node::loadMultiple($nids);
		$render_controller = \Drupal::entityTypeManager()->getViewBuilder('node');
		$render_output = $render_controller->viewMultiple($nodes, 'teaser');
		//kint($render_output);


    //$build['news_per_degree_programme']['#markup'] = $render_output;



    return array(
    	'attributes' => [
    		'class' => [
    			'grid-container'
    		],
    	],
      'content' => $render_output,
    );
  }

}
