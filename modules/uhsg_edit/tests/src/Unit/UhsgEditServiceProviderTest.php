<?php

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Tests\UnitTestCase;
use Drupal\uhsg_edit\UhsgEditServiceProvider;
use Symfony\Component\DependencyInjection\Definition;

/**
 * @group uhsg
 */
class UhsgEditServiceProviderTest extends UnitTestCase {

  /** @var \Drupal\Core\DependencyInjection\ContainerBuilder*/
  private $container;

  /** @var \Symfony\Component\DependencyInjection\Definition*/
  private $definition;

  /** @var \Drupal\uhsg_edit\UhsgEditServiceProvider*/
  private $uhsgEditServiceProvider;

  public function setUp() {
    parent::setUp();

    $this->definition = $this->prophesize(Definition::class);

    $this->container = $this->prophesize(ContainerBuilder::class);
    $this->container->hasDefinition('content_lock')->willReturn(TRUE);
    $this->container->getDefinition('content_lock')->willReturn($this->definition);

    $this->uhsgEditServiceProvider = new UhsgEditServiceProvider();
  }

  /**
   * @test
   */
  public function shouldReplaceOriginalContentLockWithCustomOne() {
    $this->definition->setClass('Drupal\uhsg_edit\ContentLock\UhsgContentLock')->shouldBeCalled();

    $this->uhsgEditServiceProvider->alter($this->container->reveal());
  }

}
