<?php

namespace Drupal\uhsg_breadcrumb;

use Drupal\Core\Breadcrumb\Breadcrumb;
use Drupal\Core\Breadcrumb\BreadcrumbBuilderInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Link;
use Drupal\Core\Path\PathMatcherInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Url;
use Drupal\uhsg_domain\DomainService;

/**
 * Class to define the Uhsg breadcrumb builder.
 */
class UhsgPathBasedBreadcrumbBuilder implements BreadcrumbBuilderInterface {
  use StringTranslationTrait;

  /**
   * Site config object.
   *
   * @var \Drupal\Core\Config\Config
   */
  protected $config;

  /**
   * The patch matcher service.
   *
   * @var \Drupal\Core\Path\PathMatcherInterface
   */
  protected $pathMatcher;

  /**
   * The domain service.
   *
   * @var \Drupal\uhsg_domain\DomainService
   */
  protected $domainService;

  /**
   * Constructs the PathBasedBreadcrumbBuilder.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory service.
   * @param \Drupal\Core\Path\PathMatcherInterface $path_matcher
   *   The path matcher service.
   * @param \Drupal\uhsg_domain\DomainService $domain_service
   *   The domain service.
   */
  public function __construct(ConfigFactoryInterface $config_factory, PathMatcherInterface $path_matcher, DomainService $domain_service) {
    $this->config = $config_factory;
    $this->pathMatcher = $path_matcher;
    $this->domainService = $domain_service;
  }

  /**
   * {@inheritdoc}
   */
  public function applies(RouteMatchInterface $route_match) {
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function build(RouteMatchInterface $route_match) {
    $breadcrumb = new Breadcrumb();
    $links = [];

    // Add the url.path.parent cache context. This code ignores the last path
    // part so the result only depends on the path parents.
    $breadcrumb->addCacheContexts(['url.path.parent', 'url.path.is_front']);

    if ($this->domainService->isStudentDomain()) {
      // Add the Studies service frontpage link.
      $links[] = new Link($this->t('Home'), Url::fromUri($this->config->get('uhsg_service_provider_details.settings')->get('home_path')));
    }

    // Add the Home link.
    if (!$this->pathMatcher->isFrontPage()) {
      $links[] = Link::createFromRoute($this->config->get('system.site')->get('name'), '<front>');
    }

    return $breadcrumb->setLinks($links);
  }

}
