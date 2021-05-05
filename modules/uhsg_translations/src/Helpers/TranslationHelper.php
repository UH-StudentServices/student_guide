<?php

namespace Drupal\uhsg_translations\Helpers;

use Drupal\locale\SourceString;
use Drupal\locale\StringDatabaseStorage;

/**
 * User interface translation helper methods.
 *
 * @package Drupal\uhsg_translations\Helpers
 */
class TranslationHelper {

  /**
   * Locale storage.
   *
   * @var \Drupal\locale\StringDatabaseStorage
   */
  private $localeStorage;

  /**
   * Constructor.
   *
   * @param Drupal\locale\StringDatabaseStorage $localeStorage
   *   Locale storage.
   */
  public function __construct(StringDatabaseStorage $localeStorage) {
    $this->localeStorage = $localeStorage;
  }

  /**
   * Add a single translation string.
   *
   * @param string $source_string
   *   Source string.
   * @param string $langcode
   *   The langcode.
   * @param string $translated_string
   *   Translated string.
   * @param bool $override_existing
   *   Override existing translation. Defaults to FALSE.
   * @param string|null $context
   *   String context or NULL for no context. Defaults to NULL.
   */
  public function addTranslation($source_string, $langcode, $translated_string, $override_existing = FALSE, $context = NULL) {
    // First check if the source string exists already.
    $string_conditions = [
      'source' => $source_string,
    ];
    if ($context) {
      $string_conditions['context'] = $context;
    }
    $string = $this->localeStorage->findString($string_conditions);

    if (is_null($string)) {
      // Create a new source, if none existed.
      $string = new SourceString();
      $string->setString($source_string);
      $string->setStorage($this->localeStorage);
      if ($context) {
        $string->context = $context;
      }
      $string->save();
    }
    elseif (!$override_existing) {
      // Search again to make sure we don't have an existing translation.
      $string_conditions['language'] = $langcode;
      $string_conditions['translated'] = FALSE;
      $string = $this->localeStorage->findString($string_conditions);
    }

    if ($string) {
      // Create translation.
      $translation = $this->localeStorage->createTranslation([
        'lid' => $string->lid,
        'language' => $langcode,
        'translation' => $translated_string,
      ]);
      $translation->save();
    }
  }

}
