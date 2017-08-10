<?php

namespace Drupal\uhsg_news\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Url;
use Drupal\uhsg_news\TargetedNewsService;
use Drupal\views\Views;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;
use Zend\Feed\Writer\Feed;

class NewsFeedController extends ControllerBase {

  /**
   * @var \Drupal\uhsg_news\TargetedNewsService
   */
  protected $targetedNewsService;

  /**
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * NewsFeedController constructor.
   *
   * @param TargetedNewsService $targetedNewsService
   * @param EntityTypeManagerInterface $entityTypeManager
   */
  public function __construct(TargetedNewsService $targetedNewsService, EntityTypeManagerInterface $entityTypeManager) {
    $this->targetedNewsService = $targetedNewsService;
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('uhsg_news.targeted_news'),
      $container->get('entity_type.manager')
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
    $nids = $this->targetedNewsService->getTargetedNewsNids(6);
    $nodes = $this->entityTypeManager->getStorage('node')->loadMultiple($nids);
    return new Response($this->generateFeed($nodes), 200, $headers);
  }

  protected function generateFeed($nodes) {

    // Create the feed
    $feed = new Feed();
    $feed->setTitle($this->t('News feed')->render());
    $feed->setDescription($this->t('Latest relevant news from Guide.')->render());

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
      // Setup item
      $url = $node->url('canonical', ['absolute' => TRUE]);
      $modified[] = $node->getChangedTime();
      $entry = $feed->createEntry();
      $entry->setTitle($node->label());
      $entry->setLink($url);
      $entry->setId($url);
      $entry->setDateCreated(new \DateTime('@' . $node->getCreatedTime()));
      $entry->setDateModified(new \DateTime('@' . $node->getChangedTime()));
      if ($node->get('body')->count() > 0) {
        $entry->setDescription(text_summary($node->get('body')->first()->getString()));
      }
      $feed->addEntry($entry);
    }

    if (empty($modified)) {
      $modified[] = REQUEST_TIME;
    }
    $feed->setDateModified(new \DateTime('@' . max($modified)));
    $feed->setFeedLink(Url::fromRoute('<current>', [], ['absolute' => TRUE])->toString(), 'atom');
    $feed->setGenerator('Guide');

    // Return the feed formatted as ATOM feed
    return $feed->export('atom');
  }
}
