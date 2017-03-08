<?php

/**
 * @file
 * Contains \Drupal\uhsg_avatar\AvatarService.
 */
 
namespace Drupal\uhsg_avatar;
use GuzzleHttp\Client;
use Drupal\user\Entity\User;

class AvatarService {

  private function getApiUrl($oodi_uid) {
    $api_base_url = 'https://student.helsinki.fi/';
    $api_path = "api/public/v1/profile/$oodi_uid";
    return $api_base_url . $api_path;
  }

  private function getOodiUid() {
    if (\Drupal::currentUser()->isAuthenticated()) {
      $user = User::load(\Drupal::currentUser()->id());
      return $user->get('field_oodi_uid')->getString();
    }
  }

  /**
   * Fetch avatar.
   */
  public function getAvatar() {
    $oodi_uid = $this->getOodiUid();
    $api_url = $this->getApiUrl($oodi_uid);

    if ($api_url) {
      $client = new \GuzzleHttp\Client();
      $api_response = $client->get($api_url, ['http_errors' => false]);

      if ($api_response->getStatusCode() == 200) {
        $body = $api_response->getBody();
        $obj = json_decode($body);
        return $obj->avatarImageUrl;
      }
    }
  }
}
