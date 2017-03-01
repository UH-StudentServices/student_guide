<?php

namespace Drupal\uhsg_edit\ContentLock;

use Drupal\content_lock\ContentLock\ContentLock;
use Drupal\user\Entity\User;

class UhsgContentLock extends ContentLock {

  /**
   * @inheritdoc
   *
   * Adds more information to the content lock message (email) in addition to
   * display name.
   */
  public function displayLockOwner($lock) {

    /** @var $user User */
    $user = User::load($lock->uid);
    $date = $this->dateFormatter->formatInterval(REQUEST_TIME - $lock->timestamp);

    return t('This content is being edited by the user @name (<a href="mailto:@email">@email</a>) and is therefore locked to prevent other users changes. This lock is in place since @date.', array(
      '@name' => $user->getDisplayName(),
      '@email' => $user->getEmail(),
      '@date' => $date,
    ));
  }
}