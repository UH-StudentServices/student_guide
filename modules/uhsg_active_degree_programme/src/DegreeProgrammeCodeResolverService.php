<?php

namespace Drupal\uhsg_active_degree_programme;

use Drupal\Core\Entity\EntityTypeManagerInterface;

class DegreeProgrammeCodeResolverService {

  /**
   * Specifies the entity type where degree programmes are stored.
   * @var string
   */
  protected $degreeProgrammeEntityType = 'taxonomy_term';

  /**
   * Used for querying by degree programme codes.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  public function __construct(EntityTypeManagerInterface $entityTypeManager) {
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * @param array $codes
   *   List of codes to be resolved.
   *
   * @return array
   *   Returns list of taxonomy IDs. If not found, then empty array.
   */
  public function resolveTidFromCodes(array $codes) {
    $entity_query = $this->entityTypeManager->getStorage($this->degreeProgrammeEntityType)->getQuery('AND');
    $entity_query->condition('field_code', $codes, 'IN');
    $entity_ids = $entity_query->execute();
    if (!empty($entity_ids)) {
      $ids = array_keys($entity_ids);
      return $ids;
    }
    return [];
  }

  /**
   * @param string $code
   *
   * @return int|null
   *   Returns ID of taxonomy term or NULL if not found.
   */
  public function resolveTidFromCode($code) {
    if (!empty($code)) {
      $ids = $this->resolveTidFromCodes([$code]);
      return $ids[0];
    }
    return NULL;
  }

}
