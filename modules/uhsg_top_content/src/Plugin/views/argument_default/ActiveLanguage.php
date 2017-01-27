<?php
namespace Drupal\uhsg_top_content\Plugin\views\argument_default;
use Drupal\views\Plugin\views\argument_default\ArgumentDefaultPluginBase;
use Drupal\Core\Language\LanguageInterface;

/**
 * The active degree programme argument default handler.
 *
 * @ingroup views_argument_default_plugins
 *
 * @ViewsArgumentDefault(
 *   id = "active_language",
 *   title = @Translation("Langcode from active language")
 * )
 */
class ActiveLanguage extends ArgumentDefaultPluginBase {
  /**
   * {@inheritdoc}
   */
  public function getArgument() {
    return \Drupal::languageManager()->getCurrentLanguage()->getId();
  }
}
