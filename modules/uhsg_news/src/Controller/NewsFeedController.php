<?php

namespace Drupal\uhsg_news\Controller;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Url;
use Drupal\uhsg_active_degree_programme\DegreeProgrammeCodeResolverService;
use Drupal\uhsg_news\NewsService;
use Drupal\views\Views;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Zend\Feed\Writer\Feed;

class NewsFeedController extends ControllerBase {

  /** @var \Drupal\Core\Entity\EntityTypeManagerInterface*/
  protected $entityTypeManager;

  /** @var \Drupal\uhsg_news\NewsService*/
  protected $newsService;

  /** @var \Drupal\Component\Datetime\TimeInterface*/
  protected $time;

  /**
   * The request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * Service for resolving degree programme codes into term IDs.
   * @var \Drupal\uhsg_active_degree_programme\DegreeProgrammeCodeResolverService
   */
  protected $degreeProgrammeCodeResolver;

  /**
   * NewsFeedController constructor.
   *
   * @param \Drupal\uhsg_news\NewsService $newsService
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
   * @param \Drupal\uhsg_active_degree_programme\DegreeProgrammeCodeResolverService degreeProgrammeCodeResolver
   */
  public function __construct(NewsService $newsService, EntityTypeManagerInterface $entityTypeManager, TimeInterface $time, RequestStack $requestStack, DegreeProgrammeCodeResolverService $degreeProgrammeCodeResolver) {
    $this->newsService = $newsService;
    $this->entityTypeManager = $entityTypeManager;
    $this->time = $time;
    $this->requestStack = $requestStack;
    $this->degreeProgrammeCodeResolver = $degreeProgrammeCodeResolver;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('uhsg_news.news'),
      $container->get('entity_type.manager'),
      $container->get('datetime.time'),
      $container->get('request_stack'),
      $container->get('uhsg_active_degree_programme.degree_programme_code_resolver')
    );
  }

  /**
   * Returns an RSS feed response using targeted news service.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   */
  public function feed() {
    $degree_programme = NULL;
    $headers = [
      'Content-Type' => 'application/rss+xml',
    ];
    // Get the nodes that we build RSS feed from
    $nids = $this->newsService->getNewsNidsHavingTids($this->getMultipleDegreeProgrammeTids(), 6);
    $nodes = $this->entityTypeManager->getStorage('node')->loadMultiple($nids);
    return new Response($this->generateFeed($nodes), 200, $headers);
  }

  /**
   * Gets multiple degree programme term ID's based on query parameter.
   * @return array
   */
  protected function getMultipleDegreeProgrammeTids() {

    // For backwards compatibility, we support single code -way
    $query_param_code = $this->requestStack->getCurrentRequest()->get('degree_programme_code');
    if (!empty($query_param_code) && !is_array($query_param_code)) {
      return $this->degreeProgrammeCodeResolver->resolveTidFromCode($query_param_code);
    }

    // Allow using multiple codes
    $query_param_codes = $this->requestStack->getCurrentRequest()->get('degree_programme_codes');
    if (!empty($query_param_codes) && is_array($query_param_codes)) {
      return $this->degreeProgrammeCodeResolver->resolveTidFromCodes($query_param_codes);
    }

    return [];
  }

  protected function generateFeed($nodes) {

    // Create the feed
    $feed = new Feed();
    $feed->setTitle($this->t('News feed')->render());
    $feed->setDescription($this->t('Latest relevant news from Instructions for students.')->render());

    // Generate URL to news (HTML page)
    $view = Views::getView('news');
    $view->setDisplay('page_1');
    $url = $view->getUrl();
    $url->setAbsolute(TRUE);
    $feed->setLink(\Drupal::urlGenerator()->generateFromRoute($url->getRouteName(), $url->getRouteParameters(), $url->getOptions()));

    // Add items
    /** @var \Drupal\node\Entity\Node $node */
    $modified = [];
    foreach ($nodes as $node) {
      // List nodes that we have access to.
      if ($node->access()) {
        // Setup item
        $translation = $node->getTranslation($this->languageManager()->getCurrentLanguage()->getId());
        $url = $translation->url('canonical', ['absolute' => TRUE]);
        $modified[] = $translation->getChangedTime();
        $entry = $feed->createEntry();
        $entry->setTitle($translation->label());
        $entry->setLink($url);
        $entry->setId($url);
        $entry->setDateCreated(new \DateTime('@' . $translation->getCreatedTime()));
        $entry->setDateModified(new \DateTime('@' . $translation->getChangedTime()));
        if ($translation->get('body')->count() > 0) {
          $entry->setDescription(text_summary($translation->get('body')
            ->first()
            ->get('value')
            ->getString()));
        }
        $feed->addEntry($entry);
      }
    }

    if (empty($modified)) {
      $modified[] = $this->time->getRequestTime();
    }
    $feed->setDateModified(new \DateTime('@' . max($modified)));
    $feed->setFeedLink(Url::fromRoute('<current>', [], ['absolute' => TRUE])->toString(), 'atom');
    $feed->setGenerator('Guide');

    // Return the feed formatted as ATOM feed
    return $feed->export('atom');
  }

}
