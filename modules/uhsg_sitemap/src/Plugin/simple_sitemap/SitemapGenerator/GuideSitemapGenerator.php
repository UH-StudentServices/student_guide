<?php

namespace Drupal\uhsg_sitemap\Plugin\simple_sitemap\SitemapGenerator;

use Drupal\simple_sitemap\Plugin\simple_sitemap\SitemapGenerator\DefaultSitemapGenerator;

/**
 * Class GuideSitemapGenerator
 *
 * @SitemapGenerator(
 *   id = "guide",
 *   label = @Translation("Guide sitemap generator"),
 *   description = @Translation("Generates a standard conform hreflang sitemap of your content."),
 * )
 */
class GuideSitemapGenerator extends DefaultSitemapGenerator {

  /**
   * {@inheritdoc}
   */
  protected function getSitemapUrlSettings() {
    if (NULL === $this->sitemapUrlSettings) {
      // Drupal\simple_sitemap\Plugin\simple_sitemap\SitemapGenerator\SitemapGeneratorBase::getSitemapUrlSettings()
      // sets the language option to LANGCODE_NOT_APPLICABLE which results in
      // all sitemap urls to never have a language prefix. We however need it
      // there as unprefixed url's dont go to Drupal in Student guide. Leaving
      // it out allows url generator to use current language.
      $this->sitemapUrlSettings = [
        'absolute' => TRUE,
        'base_url' => $this->getCustomBaseUrl(),
      ];
    }

    return $this->sitemapUrlSettings;
  }

}
