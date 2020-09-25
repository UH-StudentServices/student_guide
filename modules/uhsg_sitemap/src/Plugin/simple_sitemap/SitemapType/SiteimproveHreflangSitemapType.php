<?php

namespace Drupal\uhsg_sitemap\Plugin\simple_sitemap\SitemapType;

use Drupal\simple_sitemap\Plugin\simple_sitemap\SitemapType\SitemapTypeBase;

/**
 * Class SiteimproveHreflangSitemapType
 *
 * @SitemapType(
 *   id = "siteimprove_hreflang",
 *   label = @Translation("Siteimprove hreflang"),
 *   description = @Translation("The Siteimprove hreflang sitemap type."),
 *   sitemapGenerator = "guide",
 *   urlGenerators = {
 *     "custom",
 *     "guide_node",
 *     "arbitrary",
 *   },
 * )
 */
class SiteimproveHreflangSitemapType extends SitemapTypeBase {
}
