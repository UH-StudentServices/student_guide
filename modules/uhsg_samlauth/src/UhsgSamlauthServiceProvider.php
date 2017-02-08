<?php

namespace Drupal\uhsg_samlauth;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;

/**
 * Modify the SamlAuth service with an overridden/extended service.
 */
class UhsgSamlauthServiceProvider extends ServiceProviderBase {
  /**
   * {@inheritdoc}
   */
  public function alter(ContainerBuilder $container) {
    $definition = $container->getDefinition('samlauth.saml');
    $definition->setClass('Drupal\uhsg_samlauth\SamlService');
  }
}
