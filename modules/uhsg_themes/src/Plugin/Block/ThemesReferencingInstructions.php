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
    $renderableArray = [];
    $nodeFromParameter = \Drupal::routeMatch()->getParameter('node');

    if (isset($nodeFromParameter)) {

      // Get current page node ID.
      $nid = $nodeFromParameter->id();

      // Get themes from section paragraphs.
      $themes = $this->getSectionThemes($nid);

      // Get themes referencing this article.
      $themes += $this->getArticleThemes($nid);

      $renderableArray = $this->buildRenderableArray($themes);
    }

    return $renderableArray;
  }

  /**
   * Get paragraphs referencing the current node and then get theme nodes
   * referencing the paragraphs.
   *
   * @param int $nid Node ID.
   * @return array|int
   */
  private function getSectionThemes($nid) {
    $themes = [];

    // Get paragraphs referencing the current node.
    $query = \Drupal::entityQuery('paragraph')
      ->condition('status', 1)
      ->condition('type', 'theme_section')
      ->condition('field_theme_section_instructions', $nid)
      ->sort('created', 'DESC');

    $paragraphs = $query->execute();

    // Get theme nodes referencing the paragraphs.
    if (!empty($paragraphs)) {
      $query = \Drupal::entityQuery('node')
        ->condition('status', 1)
        ->condition('type', 'theme')
        ->condition('field_theme_section', $paragraphs, 'IN')
        ->sort('created', 'DESC');

      $themes = $query->execute();
    }

    return $themes;
  }

  /**
   * Get theme nodes referencing the paragraphs.
   *
   * @param int $nid Node ID.
   * @return array|int
   */
  private function getArticleThemes($nid) {
    $query = \Drupal::entityQuery('node')
      ->condition('status', 1)
      ->condition('type', 'theme')
      ->condition('field_theme_articles', $nid, 'IN')
      ->sort('created', 'DESC');

    return $query->execute();
  }

  /**
   * @param $themes
   * @return array
   */
  private function buildRenderableArray($themes) {
    $renderableArray = [];

    if (!empty($themes)) {
      $links = [];
      $nodes = Node::loadMultiple($themes);

      foreach ($nodes as $node) {
        $translation = \Drupal::entityManager()->getTranslationFromContext($node);

        $links[] = Link::createFromRoute(
          $translation->getTitle(),
          'entity.node.canonical',
          ['node' => $node->id()],
          ['attributes' => ['class' => 'list-of-links__link button--action icon--arrow-right']]
        );
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
