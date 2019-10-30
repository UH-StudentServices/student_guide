<?php

namespace Drupal\uhsg_samlauth;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Modify the SamlAuth service with an overridden/extended service.
 */
class UhsgSamlauthServiceProvider extends ServiceProviderBase {

  /**
   * {@inheritdoc}
   */
  public function alter(ContainerBuilder $container) {
    // Disable overrides temporarily.
    // $definition = $container->getDefinition('samlauth.saml');
    // $definition->setClass('Drupal\uhsg_samlauth\SamlService');
    // $definition->addArgument(new Reference('request_stack'));
    // $definition->addArgument(new Reference('session'));
    // $definition->addArgument(new Reference('path.validator'));
  }

}
