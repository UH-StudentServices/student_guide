<?php

namespace Drupal\uhsg_themes\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Link;
use Drupal\node\Entity\Node;

/**
 * Provides a 'themes_referencing_instructions' block.
 *
 * @Block(
 *  id = "themes_referencing_instructions",
 *  admin_label = @Translation("Themes referencing instructions"),
 * )
 */
class ThemesReferencingInstructions extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {

    // get current page nid
    $nid = \Drupal::routeMatch()->getParameter('node')->id();

    // get paragraphs referencing the current node
    $query = \Drupal::entityQuery('paragraph')
      ->condition('status', 1)
      ->condition('type', 'theme_section')
      ->condition('field_theme_section_instructions', $nid)
      ->sort('created', 'DESC');

    $paragraphs = $query->execute();

    // get theme nodes referencing the paragraphs
    $themes = [];

    if (!empty($paragraphs)) {
      $query = \Drupal::entityQuery('node')
        ->condition('status', 1)
        ->condition('type', 'theme')
        ->condition('field_theme_section', $paragraphs, 'IN')
        ->sort('created', 'DESC');

      $themes = $query->execute();
    }

    $renderableArray = [];

    if (!empty($themes)) {
      $links = [];
      $nodes = Node::loadMultiple($themes);

      foreach ($nodes as $node) {
        $translation = \Drupal::entityManager()->getTranslationFromContext($node);
        $links[] = Link::createFromRoute($translation->getTitle(), 'entity.node.canonical', ['node' => $node->id()],
        ['attributes' => ['class' => 'list-of-links__link button--action icon--arrow-right']]);
      }

      $renderableArray = [
        '#attributes' => [
          'class' => ['list-of-links'],
        ],
        '#cache' => [
          'tags' => ['node_list'],
        ],
        '#theme' => 'item_list',
        '#type' => 'ul',
        '#items' => $links,
        '#title' => t('Themes'),
      ];
    }

    return $renderableArray;
  }
}
