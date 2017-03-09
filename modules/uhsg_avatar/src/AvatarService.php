<?php

/**
 * @file
 * Contains \Drupal\uhsg_avatar\AvatarService.
 */
 
namespace Drupal\uhsg_avatar;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Drupal\user\Entity\User;

class AvatarService {

  /**
   * Check if the image is default image.
   */
  public function isDefault($url) {
    return $url == \Drupal::config('uhsg_avatar.config')->get('default_image_url');
  }

  /**
   * Fetch avatar.
   */
  public function getAvatar() {
    $oodi_uid = $this->getOodiUid();
    $api_url = $this->getApiUrl($oodi_uid);

    if ($api_url) {
      $client = new \GuzzleHttp\Client();

      try {
        $api_response = $client->get($api_url);
        if ($api_response->getStatusCode() == 200) {
          $body = $api_response->getBody();
          $obj = json_decode($body);
          return $obj->avatarImageUrl;
        }
      }
      catch (RequestException $e) {
        return NULL;
      }
    }
  }

  private function getOodiUid() {
    if (\Drupal::currentUser()->isAuthenticated()) {
      $user = User::load(\Drupal::currentUser()->id());
      return $user->get('field_oodi_uid')->getString();
    }
  }

  private function getApiUrl($oodi_uid) {
    $api_base_url = \Drupal::config('uhsg_avatar.config')->get('api_base_url');
    $api_path = \Drupal::config('uhsg_avatar.config')->get('api_path');
    return $api_base_url . $api_path . $oodi_uid;
  }
}
