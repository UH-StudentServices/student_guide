<?php

namespace Drupal\uhsg_themes\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Link;
use Drupal\node\Entity\Node;
use Drupal\views\Views;

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
    $lang = \Drupal::languageManager()->getCurrentLanguage()->getId();
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
    foreach ($paragraphs as $paragraph_id) {
      $query = \Drupal::entityQuery('node')
        ->condition('status', 1)
        ->condition('type', 'theme')
        ->condition('field_theme_section', $paragraph_id)
        ->sort('created', 'DESC');
      $result = $query->execute();
      $themes = $themes + $result;
    }

    if (!empty($themes)) {
      $links = [];
      foreach ($themes as $nid) {
        $node = Node::load($nid);
        $translation = \Drupal::entityManager()->getTranslationFromContext($node);
        $link = Link::createFromRoute($translation->getTitle(), 'entity.node.canonical', ['node' => $nid],
        ['attributes' => ['class' => 'list-of-links__link button--action icon--arrow-right']]);
      }
    }

    return [
      '#attributes' => [
        'class' => ['list-of-links'],
      ],
      '#cache' => [
        'tags' => ['node_list'],
      ],
      '#markup' => $link->toString(),
    ];

  }
}
