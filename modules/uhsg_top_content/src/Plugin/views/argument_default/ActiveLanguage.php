<?php
namespace Drupal\uhsg_top_content\Plugin\views\argument_default;
use Drupal\views\Plugin\views\argument_default\ArgumentDefaultPluginBase;
use Drupal\Core\Cache\CacheableDependencyInterface;
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
class ActiveLanguage extends ArgumentDefaultPluginBase implements CacheableDependencyInterface {
  /**
   * {@inheritdoc}
   */
  public function getArgument() {
    return \Drupal::languageManager()->getCurrentLanguage()->getId();
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheMaxAge() {
    return 0;
  }

  /**
   * {@inheritdoc}
   * This is always added by views but we are adding it here just in case.
   */
  public function getCacheContexts() {
    return ['languages:language_interface'];
  }
}
