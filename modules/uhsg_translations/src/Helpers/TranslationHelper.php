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
   */
  public function addTranslation($source_string, $langcode, $translated_string, $override_existing = FALSE) {
    // First check if the source string exists already.
    $string = $this->localeStorage->findString([
      'source' => $source_string,
    ]);

    if (is_null($string)) {
      // Create a new source, if none existed.
      $string = new SourceString();
      $string->setString($source_string);
      $string->setStorage($this->localeStorage);
      $string->save();
    }
    elseif (!$override_existing) {
      // Search again to make sure we don't have an existing translation.
      $string = $this->localeStorage->findString([
        'source' => $source_string,
        'language' => $langcode,
        'translated' => FALSE,
      ]);
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
