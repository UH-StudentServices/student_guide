<?php

namespace Drupal\uhsg_obar;

use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Session\AccountInterface;
use Firebase\JWT\JWT as Firebase_JWT;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Routing\UrlGeneratorInterface;

class Jwt {

  /**
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected $config;

  /**
   * @var \Drupal\Core\Routing\UrlGeneratorInterface
   */
  protected $urlGenerator;

  /**
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * Jwt constructor.
   * @param \Drupal\Core\Config\ConfigFactory $configFactory
   * @param \Drupal\Core\Session\AccountInterface $user
   * @param \Drupal\Core\Routing\UrlGeneratorInterface $urlGenerator
   * @param \Drupal\Core\Language\LanguageManagerInterface $languageManager
   */
  public function __construct(ConfigFactory $config, AccountInterface $user, UrlGeneratorInterface $urlGenerator, LanguageManagerInterface $languageManager) {
    $this->config = $config->get('uhsg_obar.settings');
    $this->user = $user;
    $this->urlGenerator = $urlGenerator;
    $this->languageManager = $languageManager;
  }

  public function generateToken() {
    return Firebase_JWT::encode($this->getPayload(), $this->getKeyContents(), 'RS256');
  }

  private function getKeyContents() {
    $private_key_filepath = $this->config->get('private_key_path');
    if (!empty($private_key_filepath)) {
      return file_get_contents($private_key_filepath);
    }
    throw new \Exception('Private key path configuration is missing.');
  }

  private function getPayload() {
    return (object) [
      'loginEndpoint' => $this->urlGenerator->generateFromRoute('samlauth.saml_controller_login'),
      'logoutEndpoint' => $this->urlGenerator->generateFromRoute('samlauth.saml_controller_logout'),
      'user' => NULL,
      'currentLang' => $this->languageManager->getCurrentLanguage()->getId(),
      'languageSelectEndpoints' => '',
    ];
  }

}
