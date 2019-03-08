<?php

namespace Drupal\uhsg_eduviewer;

use Drupal\Core\Language\LanguageManager;
use Drupal\uhsg_active_degree_programme\ActiveDegreeProgrammeService;

class Eduviewer {

  const INVALID_DEGREE_PROGRAMME_CODES = ['KH20_001', 'MH30_003'];

  /** @var \Drupal\uhsg_active_degree_programme\ActiveDegreeProgrammeService*/
  private $activeDegreeProgrammeService;

  /** @var \Drupal\Core\Language\LanguageManager*/
  private $languageManager;

  public function __construct(ActiveDegreeProgrammeService $activeDegreeProgrammeService, LanguageManager $languageManager) {
    $this->activeDegreeProgrammeService = $activeDegreeProgrammeService;
    $this->languageManager = $languageManager;
  }

  /**
   * Returns Eduviewer markup matching the active degree programme code and the
   * current UI language, for example:
   *
   * <div id="eduviewer-root" degree-program-id="CODE_123" lang="fi"></div>
   *
   * Returns NULL if the degree programme code is not valid.
   *
   * @return null|string
   */
  public function getMarkup() {
    $markup = NULL;
    $activeDegreeProgrammeCode = $this->getActiveDegreeProgrammeCode();

    if ($this->isValidDegreeProgrammeCode($activeDegreeProgrammeCode)) {
      $language = $this->languageManager->getCurrentLanguage()->getId();
      $markup = "<div id=\"eduviewer-root\" degree-program-id=\"$activeDegreeProgrammeCode\" lang=\"$language\" disable-global-style=\"true\"></div>";
    }

    return $markup;
  }

  /**
   * Returns the active degree programme code. For codes that combine the degree
   * programme code and the study track code, return only the degree programme
   * code (for example: "KH60_001SH60_039" -> "KH60_001"). All of the degree
   * programme codes have a maximum of eight characters for the degree programme
   * code part. Thus the algorithm simply returns the first eight characters of
   * a given code.
   *
   * @return null|string
   */
  private function getActiveDegreeProgrammeCode() {
    $activeDegreeProgrammeCode = $this->activeDegreeProgrammeService->getCode();

    return $activeDegreeProgrammeCode ? mb_substr($activeDegreeProgrammeCode, 0, 8) : NULL;
  }

  /**
   * @param $degreeProgrammeCode
   * @return bool TRUE if the code is not empty and is not 'KH20_001' or
   * 'MH30_003', otherwise FALSE (see HUB-342).
   */
  private function isValidDegreeProgrammeCode($degreeProgrammeCode) {
    return !empty($degreeProgrammeCode) && !in_array($degreeProgrammeCode, self::INVALID_DEGREE_PROGRAMME_CODES);
  }
}
