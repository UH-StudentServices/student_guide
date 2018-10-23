<?php

namespace Drupal\uhsg_some_links;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Link;
use Drupal\Core\Url;

/**
 * Defines a class to build a listing of Some links entities.
 *
 * @ingroup uhsg_some_links
 */
class SomeLinksListBuilder extends EntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = $this->t('Some links ID');
    $header['name'] = $this->t('Name');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /* @var $entity \Drupal\uhsg_some_links\Entity\SomeLinks */
    $row['id'] = $entity->id();
    $row['name'] = new Link(
      $entity->label(),
      new Url(
        'entity.some_links.edit_form', [
          'some_links' => $entity->id(),
        ]
      )
    );
    return $row + parent::buildRow($entity);
  }

}
