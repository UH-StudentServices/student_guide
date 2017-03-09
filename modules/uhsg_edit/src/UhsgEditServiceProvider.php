<?php

namespace Drupal\uhsg_edit;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;

/**
 * Modify the ContentLock service with an overridden/extended service.
 */
class UhsgEditServiceProvider extends ServiceProviderBase {

  /**
   * {@inheritdoc}
   */
  public function alter(ContainerBuilder $container) {
    if ($container->hasDefinition('content_lock')) {
      $definition = $container->getDefinition('content_lock');
      $definition->setClass('Drupal\uhsg_edit\ContentLock\UhsgContentLock');
    }
  }
}
