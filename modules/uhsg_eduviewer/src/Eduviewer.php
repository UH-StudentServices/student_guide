<?php

namespace Drupal\uhsg_eduviewer;

use Drupal\Core\Language\LanguageManager;
use Drupal\uhsg_active_degree_programme\ActiveDegreeProgrammeService;

class Eduviewer {

  const INVALID_DEGREE_PROGRAMME_CODES = ['KH20_001', 'MH30_003'];

  /** @var ActiveDegreeProgrammeService */
  private $activeDegreeProgrammeService;

  /** @var LanguageManager */
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
    $activeDegreeProgrammeCode = $this->activeDegreeProgrammeService->getCode();

    if ($this->isValidDegreeProgrammeCode($activeDegreeProgrammeCode)) {
      $language = $this->languageManager->getCurrentLanguage()->getId();
      $markup = "<div id=\"eduviewer-root\" degree-program-id=\"$activeDegreeProgrammeCode\" lang=\"$language\"></div>";
    }

    return $markup;
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
