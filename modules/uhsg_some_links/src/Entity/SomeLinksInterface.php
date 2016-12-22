<?php

namespace Drupal\uhsg_some_links\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Some links entities.
 *
 * @ingroup uhsg_some_links
 */
interface SomeLinksInterface extends  ContentEntityInterface, EntityChangedInterface, EntityOwnerInterface {

  // Add get/set methods for your configuration properties here.

  /**
   * Gets the Some links name.
   *
   * @return string
   *   Name of the Some links.
   */
  public function getName();

  /**
   * Sets the Some links name.
   *
   * @param string $name
   *   The Some links name.
   *
   * @return \Drupal\uhsg_some_links\Entity\SomeLinksInterface
   *   The called Some links entity.
   */
  public function setName($name);

  /**
   * Gets the Some links creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Some links.
   */
  public function getCreatedTime();

  /**
   * Sets the Some links creation timestamp.
   *
   * @param int $timestamp
   *   The Some links creation timestamp.
   *
   * @return \Drupal\uhsg_some_links\Entity\SomeLinksInterface
   *   The called Some links entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Returns the Some links published status indicator.
   *
   * Unpublished Some links are only visible to restricted users.
   *
   * @return bool
   *   TRUE if the Some links is published.
   */
  public function isPublished();

  /**
   * Sets the published status of a Some links.
   *
   * @param bool $published
   *   TRUE to set this Some links to published, FALSE to set it to unpublished.
   *
   * @return \Drupal\uhsg_some_links\Entity\SomeLinksInterface
   *   The called Some links entity.
   */
  public function setPublished($published);

}
