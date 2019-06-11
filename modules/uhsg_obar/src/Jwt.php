<?php

namespace Drupal\uhsg_obar;

use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Session\AccountInterface;
use Firebase\JWT\JWT as Firebase_JWT;

class Jwt {

  /**
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected $config;

  /**
   * Jwt constructor.
   * @param \Drupal\Core\Config\ConfigFactory $configFactory
   * @param \Drupal\Core\Session\AccountInterface $user
   */
  public function __construct(ConfigFactory $config, AccountInterface $user) {
    $this->config = $config->get('uhsg_obar.settings');
    $this->user = $user;
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
      'loginEndpoint' => '',
      'logoutEndpoint' => '',
      'user' => NULL,
      'currentLang' => '',
      'languageSelectEndpoints' => '',
    ];
  }

}
