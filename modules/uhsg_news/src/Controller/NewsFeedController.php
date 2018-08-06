<?php

namespace Drupal\uhsg_news\Controller;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Url;
use Drupal\uhsg_news\NewsService;
use Drupal\views\Views;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;
use Zend\Feed\Writer\Feed;

class NewsFeedController extends ControllerBase {

  /** @var \Drupal\Core\Entity\EntityTypeManagerInterface */
  protected $entityTypeManager;

  /** @var \Drupal\uhsg_news\NewsService */
  protected $newsService;

  /** @var TimeInterface */
  protected $time;

  /**
   * NewsFeedController constructor.
   *
   * @param NewsService $newsService
   * @param EntityTypeManagerInterface $entityTypeManager
   */
  public function __construct(NewsService $newsService, EntityTypeManagerInterface $entityTypeManager, TimeInterface $time) {
    $this->newsService = $newsService;
    $this->entityTypeManager = $entityTypeManager;
    $this->time = $time;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('uhsg_news.news'),
      $container->get('entity_type.manager'),
      $container->get('datetime.time')
    );
  }

  /**
   * Returns an RSS feed response using targeted news service.
   *
   * @return Response
   */
  public function feed() {
    $degree_programme = NULL;
    $headers = [
      'Content-Type' => 'application/rss+xml'
    ];
    // Get the nodes that we build RSS feed from
    $nids = $this->newsService->getNewsNids(6);
    $nodes = $this->entityTypeManager->getStorage('node')->loadMultiple($nids);
    return new Response($this->generateFeed($nodes), 200, $headers);
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
    $modified = array();
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
