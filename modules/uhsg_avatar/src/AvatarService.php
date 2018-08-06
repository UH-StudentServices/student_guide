<?php

namespace Drupal\uhsg_avatar;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\Logger\LoggerChannel;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\user\Entity\User;
use GuzzleHttp\Client;

class AvatarService {

  const CACHE_EXPIRE_SECONDS = 21600; // 6 hours.
  const CACHE_KEY_PREFIX = 'avatar-';

  /** @var CacheBackendInterface */
  protected $cache;

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

  /** @var TimeInterface */
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
    $oodiUid = $this->getOodiUid();

    if ($oodiUid) {
      $avatarUrl = $this->getAvatarUrlFromCache($oodiUid);
      $apiUrl = $this->getApiUrl($oodiUid);

      if ($apiUrl && !$avatarUrl) {
        try {
          $apiResponse = $this->client->get($apiUrl);

          if ($apiResponse->getStatusCode() == 200) {
            $body = $apiResponse->getBody();
            $decodedBody = json_decode($body);
            $avatarUrl = $decodedBody->avatarImageUrl;
            $this->setAvatarUrlToCache($oodiUid, $avatarUrl);
          }
        } catch (\Exception $e) {
          $this->logger->error($e->getMessage());
        }
      }
    }

    return $avatarUrl;
  }

  /**
   * @param int $id
   * @return User
   */
  protected function loadUser($id) {
    return User::load($id);
  }

  private function getAvatarUrlFromCache($oodiUid) {
    $cacheKey = $this->getCacheKey($oodiUid);
    $avatarUrl = $cacheKey ? $this->cache->get($cacheKey) : NULL;

    return $avatarUrl ? $avatarUrl->data : NULL;
  }

  private function getOodiUid() {
    $oodiUid = NULL;

    if ($this->currentUser->isAuthenticated() && $this->currentUser->id() != 1) {
      $user = $this->loadUser($this->currentUser->id());
      $oodiUid = $user->get('field_oodi_uid')->getString();
    }

    return $oodiUid;
  }

  private function getApiUrl($oodiUid) {
    $apiBaseUrl = $this->config->get('api_base_url');
    $apiPath = $this->config->get('api_path');

    return isset($apiBaseUrl, $apiPath, $oodiUid) ? $apiBaseUrl . $apiPath . $oodiUid : NULL;
  }

  private function setAvatarUrlToCache($oodiUid, $avatarUrl) {
    $this->cache->set($this->getCacheKey($oodiUid), $avatarUrl, $this->getCacheExpireTimestamp());
  }

  private function getCacheKey($oodiUid) {
    return $oodiUid ? self::CACHE_KEY_PREFIX . $oodiUid : NULL;
  }

  private function getCacheExpireTimestamp() {
    return $this->time->getRequestTime() + self::CACHE_EXPIRE_SECONDS;
  }
}
