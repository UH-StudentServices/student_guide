<?php

namespace Drupal\uhsg_samlauth;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;
use OneLogin\Saml2\Utils;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Modify the SamlAuth service with an overridden/extended service.
 */
class UhsgSamlauthServiceProvider extends ServiceProviderBase {

  /**
   * {@inheritdoc}
   */
  public function alter(ContainerBuilder $container) {

    // Configure onelogin/php-saml to use proxy.
    Utils::setProxyVars(TRUE);

    // Disable overrides temporarily.
    // $definition = $container->getDefinition('samlauth.saml');
    // $definition->setClass('Drupal\uhsg_samlauth\SamlService');
    // $definition->addArgument(new Reference('request_stack'));
    // $definition->addArgument(new Reference('session'));
    // $definition->addArgument(new Reference('path.validator'));
  }

}
