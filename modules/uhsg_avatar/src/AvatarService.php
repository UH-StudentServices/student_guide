<?php

namespace Drupal\uhsg_avatar;

use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\Logger\LoggerChannel;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\user\Entity\User;
use GuzzleHttp\Client;

class AvatarService {

  /** @var Client */
  protected $client;

  /** @var ImmutableConfig */
  protected $config;

  /** @var ConfigFactory */
  protected $configFactory;

  /** @var AccountProxyInterface */
  protected $currentUser;

  /** @var LoggerChannel */
  protected $logger;

  public function __construct(ConfigFactory $configFactory, AccountProxyInterface $currentUser, Client $client, LoggerChannel $logger) {
    $this->config = $configFactory->get('uhsg_avatar.config');
    $this->currentUser = $currentUser;
    $this->client = $client;
    $this->logger = $logger;
  }

  /**
   * Check if the image is default image.
   *
   * @param string $url Image URL.
   * @return bool Is default image?
   */
  public function isDefault($url) {
    return $url == $this->config->get('default_image_url');
  }

  /**
   * Fetch avatar.
   */
  public function getAvatar() {
    $oodiUid = $this->getOodiUid();
    $apiUrl = $this->getApiUrl($oodiUid);
    $avatarUrl = NULL;

    if ($apiUrl && $oodiUid) {
      try {
        $apiResponse = $this->client->get($apiUrl);

        if ($apiResponse->getStatusCode() == 200) {
          $body = $apiResponse->getBody();
          $decodedBody = json_decode($body);
          $avatarUrl = $decodedBody->avatarImageUrl;
        }
      }
      catch (\Exception $e) {
        $this->logger->error($e->getMessage());
      }
    }

    return $avatarUrl;
  }

  private function getOodiUid() {
    $oodiUid = NULL;

    if ($this->currentUser->isAuthenticated()) {

      /** @var $user User */
      $user = User::load($this->currentUser->id());
      $oodiUid = $user->get('field_oodi_uid')->getString();
    }

    return $oodiUid;
  }

  private function getApiUrl($oodiUid) {
    $apiBaseUrl = $this->config->get('api_base_url');
    $apiPath = $this->config->get('api_path');

    return $apiBaseUrl . $apiPath . $oodiUid;
  }
}
