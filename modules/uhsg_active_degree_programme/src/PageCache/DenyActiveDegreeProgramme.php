<?php

namespace Drupal\uhsg_active_degree_programme\PageCache;

use Drupal\Core\PageCache\ResponsePolicyInterface;
use Drupal\uhsg_active_degree_programme\ActiveDegreeProgrammeService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DenyActiveDegreeProgramme implements ResponsePolicyInterface {

  /**
   * Service that holds information about active degree programme.
   *
   * @var ActiveDegreeProgrammeService
   */
  protected $activeDegreeProgrammeService;

  public function __construct(ActiveDegreeProgrammeService $activeDegreeProgrammeService) {
    $this->activeDegreeProgrammeService = $activeDegreeProgrammeService;
  }

  /**
   * @inheritdoc
   */
  public function check(Response $response, Request $request) {
    // If we find degree programme, do not allow page caching.
    if ($this->activeDegreeProgrammeService->getId()) {
      return self::DENY;
    }
  }

}
