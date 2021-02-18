<?php

namespace Drupal\uhsg_avatar;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Logger\LoggerChannel;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\user\Entity\User;
use GuzzleHttp\Client;

class AvatarService {

  // 6 hours.
  const CACHE_EXPIRE_SECONDS = 21600;
  const CACHE_KEY_PREFIX = 'avatar-';

  /** @var \Drupal\Core\Cache\CacheBackendInterface*/
  protected $cache;

  /** @var \GuzzleHttp\Client*/
  protected $client;

  /** @var \Drupal\Core\Config\ImmutableConfig*/
  protected $config;

  /** @var \Drupal\Core\Config\ConfigFactory*/
  protected $configFactory;

  /** @var \Drupal\Core\Session\AccountProxyInterface*/
  protected $currentUser;

  /** @var \Drupal\Core\Logger\LoggerChannel*/
  protected $logger;

  /** @var \Drupal\Component\Datetime\TimeInterface*/
  protected $time;

  public function __construct(ConfigFactory $configFactory, AccountProxyInterface $currentUser, Client $client, LoggerChannel $logger, CacheBackendInterface $cache, TimeInterface $time) {
    $this->config = $configFactory->get('uhsg_avatar.config');
    $this->currentUser = $currentUser;
    $this->client = $client;
    $this->logger = $logger;
    $this->cache = $cache;
    $this->time = $time;
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
    $avatarUrl = NULL;
    $hyPersonId = $this->gethyPersonId();

    if ($hyPersonId) {
      $avatarUrl = $this->getAvatarUrlFromCache($hyPersonId);
      $apiUrl = $this->getApiUrl($hyPersonId);

      if ($apiUrl && !$avatarUrl) {
        try {
          $apiResponse = $this->client->get($apiUrl);

          if ($apiResponse->getStatusCode() == 200) {
            $body = $apiResponse->getBody();
            $decodedBody = json_decode($body);
            $avatarUrl = $decodedBody->avatarImageUrl;
            $this->setAvatarUrlToCache($hyPersonId, $avatarUrl);
          }
        }
        catch (\Exception $e) {
          $this->logger->error($e->getMessage());
        }
      }
    }

    return $avatarUrl;
  }

  /**
   * @param int $id
   * @return \Drupal\user\Entity\User
   */
  protected function loadUser($id) {
    return User::load($id);
  }

  private function getAvatarUrlFromCache($hyPersonId) {
    $cacheKey = $this->getCacheKey($hyPersonId);
    $avatarUrl = $cacheKey ? $this->cache->get($cacheKey) : NULL;

    return $avatarUrl ? $avatarUrl->data : NULL;
  }

  private function getHyPersonId() {
    $hyPersonId = NULL;

    if ($this->currentUser->isAuthenticated() && $this->currentUser->id() != 1) {
      $user = $this->loadUser($this->currentUser->id());
      $hyPersonId = $user->get('field_hypersonid')->getString();
    }

    return $hyPersonId;
  }

  private function getApiUrl($hyPersonId) {
    $apiBaseUrl = $this->config->get('api_base_url');
    $apiPath = $this->config->get('api_path');

    return isset($apiBaseUrl, $apiPath, $hyPersonId) ? $apiBaseUrl . $apiPath . $hyPersonId : NULL;
  }

  private function setAvatarUrlToCache($hyPersonId, $avatarUrl) {
    $this->cache->set($this->getCacheKey($hyPersonId), $avatarUrl, $this->getCacheExpireTimestamp());
  }

  private function getCacheKey($hyPersonId) {
    return $hyPersonId ? self::CACHE_KEY_PREFIX . $hyPersonId : NULL;
  }

  private function getCacheExpireTimestamp() {
    return $this->time->getRequestTime() + self::CACHE_EXPIRE_SECONDS;
  }

}
