<?php

/**
 * @file
 * Contains \Drupal\uhsg_avatar\AvatarService.
 */
 
namespace Drupal\uhsg_avatar;
use GuzzleHttp\Client;

class AvatarService {

  private function apiUrl($oodi_uid) {
    $api_base_url = 'https://student.helsinki.fi/';
    $api_path = "api/public/v1/profile/$oodi_uid";
    return $api_base_url . $api_path;
  }

  /**
   * Fetch avatar.
   */
  public function fetchAvatar($oodi_uid) {
    $api_url = $this->apiUrl($oodi_uid);

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
