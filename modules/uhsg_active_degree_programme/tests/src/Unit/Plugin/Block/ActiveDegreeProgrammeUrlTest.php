<?php

use Drupal\Core\Cache\Context\CacheContextsManager;
use Drupal\Core\Session\AccountInterface;
use Drupal\Tests\UnitTestCase;
use Drupal\uhsg_active_degree_programme\Plugin\Block\ActiveDegreeProgrammeUrl;
use Prophecy\Argument;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @group uhsg
 */
class ActiveDegreeProgrammeUrlTest extends UnitTestCase {

  const COPY_ACTIVE_DEGREE_PROGRAMME_URL = 'copy active degree programme url';

  /** @var AccountInterface */
  private $account;

  /** @var ActiveDegreeProgrammeUrl */
  private $activeDegreeProgrammeUrl;

  /** @var CacheContextsManager */
  private $cacheContextsManager;

  /** @var ContainerInterface */
  private $container;

  public function setUp() {
    parent::setUp();

    $this->account = $this->prophesize(AccountInterface::class);
    $this->activeDegreeProgrammeUrl = new ActiveDegreeProgrammeUrlTestDouble();

    $this->cacheContextsManager = $this->prophesize(CacheContextsManager::class);
    $this->cacheContextsManager->assertValidTokens(Argument::any())->willReturn(TRUE);

    $this->container = $this->prophesize(ContainerInterface::class);
    $this->container->get('cache_contexts_manager')->willReturn($this->cacheContextsManager->reveal());

    Drupal::setContainer($this->container->reveal());
  }

  /**
   * @test
   */
  public function shouldAllowAccessWhenUserHasPermission() {
    $this->account->hasPermission(self::COPY_ACTIVE_DEGREE_PROGRAMME_URL)->willReturn(TRUE);

    $this->assertTrue($this->activeDegreeProgrammeUrl->access($this->account->reveal()));
  }

  /**
   * @test
   */
  public function shouldDenyAccessWhenUserDoesNotHavePermission() {
    $this->account->hasPermission(self::COPY_ACTIVE_DEGREE_PROGRAMME_URL)->willReturn(FALSE);

    $this->assertFalse($this->activeDegreeProgrammeUrl->access($this->account->reveal()));
  }

  /**
   * @test
   */
  public function shouldBuildRenderableOutput() {
    $expectedRenderableOutput = [
      'content' => [
        'button' => [
          '#type' => 'button',
          '#value' => 'Copy URL',
          '#attributes' => [
            'id' => 'copy-url'
          ],
          '#attached' => [
            'library' => [
              'uhsg_active_degree_programme/copy_url'
            ],
            'drupalSettings' => [
              'uhsg_active_degree_programme' => [
                'selector' => '[rel="shortlink-with-degree-programme"]'
              ]
            ]
          ]
        ]
      ]
    ];

    $this->assertEquals($expectedRenderableOutput, $this->activeDegreeProgrammeUrl->build());
  }
}

/**
 * Test double for overriding difficult to test methods.
 */
class ActiveDegreeProgrammeUrlTestDouble extends ActiveDegreeProgrammeUrl {

  public function __construct(array $configuration = [], $plugin_id = NULL, $plugin_definition = NULL) {
    // Do nothing.
  }

  public function t($string, array $args = [], array $options = []) {
    return $string;
  }
}