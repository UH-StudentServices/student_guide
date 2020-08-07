<?php

namespace Drupal\uhsg_sitemap;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;

/**
 * Modifies the language manager service.
 */
class UhsgSitemapServiceProvider extends ServiceProviderBase {

  /**
   * {@inheritdoc}
   */
  public function alter(ContainerBuilder $container) {
    // Overrides simple_sitemap.path_processor.variant.in and
    // simple_sitemap.path_processor.variant.out to disable both
    // path processors. We don't need them as we want our sitemap
    // url's to always have language prefix in them.
    $definition = $container->getDefinition('simple_sitemap.path_processor.variant.in');
    $definition->clearTag('path_processor_inbound');

    $definition = $container->getDefinition('simple_sitemap.path_processor.variant.out');
    $definition->clearTag('path_processor_outbound');
  }

}
