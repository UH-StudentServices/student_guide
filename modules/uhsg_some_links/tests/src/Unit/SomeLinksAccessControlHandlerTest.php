<?php

use Drupal\Core\Cache\Context\CacheContextsManager;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Tests\UnitTestCase;
use Drupal\uhsg_some_links\SomeLinksAccessControlHandler;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Prophecy\Argument;

/**
 * @group uhsg
 */
class SomeLinksAccessControlHandlerTest extends UnitTestCase {

  /** @var \Drupal\Core\Session\AccountInterface*/
  private $account;

  /** @var \Drupal\Core\Cache\Context\CacheContextsManager*/
  private $cacheContextsManager;

  /** @var \Symfony\Component\DependencyInjection\ContainerInterface*/
  private $container;

  /** @var \Drupal\Core\Entity\EntityInterface*/
  private $entity;

  /** @var \Drupal\Core\Entity\EntityTypeInterface*/
  private $entityType;

  /** @var \Drupal\Core\Language\LanguageInterface*/
  private $language;

  /** @var \Drupal\Core\Extension\ModuleHandlerInterface*/
  private $moduleHandler;

  /** @var \Drupal\uhsg_some_links\SomeLinksAccessControlHandler*/
  private $someLinksAccessControlHandler;

  public function setUp() {
    parent::setUp();

    $this->account = $this->prophesize(AccountInterface::class);
    $this->account->id()->willReturn('accountId');

    $this->cacheContextsManager = $this->prophesize(CacheContextsManager::class);
    $this->cacheContextsManager->assertValidTokens(Argument::any())->willReturn(TRUE);

    $this->moduleHandler = $this->prophesize(ModuleHandlerInterface::class);
    $this->moduleHandler->invokeAll(Argument::any(), Argument::any())->willReturn([]);

    $this->container = $this->prophesize(ContainerInterface::class);
    $this->container->get('module_handler')->willReturn($this->moduleHandler);
    $this->container->get('cache_contexts_manager')->willReturn($this->cacheContextsManager);

    $this->language = $this->prophesize(LanguageInterface::class);

    $this->entity = $this->prophesize(EntityInterface::class);
    $this->entity->getEntityTypeId()->willReturn('entityTypeID');
    $this->entity->language()->willReturn($this->language);
    $this->entity->uuid()->willReturn('uuid');

    $this->entityType = $this->prophesize(EntityTypeInterface::class);

    Drupal::setContainer($this->container->reveal());

    $this->someLinksAccessControlHandler = new SomeLinksAccessControlHandler($this->entityType->reveal());
  }

  /**
   * @test
   */
  public function shouldReturnFalseOnUnknownOperation() {
    $accessResult = $this->someLinksAccessControlHandler->access($this->entity->reveal(), 'unknown operation', $this->account->reveal());

    $this->assertFalse($accessResult);
  }

  /**
   * @test
   */
  public function shouldReturnTrueWhenTheAccountHasPermissionToDeleteSomeLinkEntities() {
    $this->account->hasPermission('delete some links entities')->willReturn(TRUE);

    $accessResult = $this->someLinksAccessControlHandler->access($this->entity->reveal(), 'delete', $this->account->reveal());

    $this->assertTrue($accessResult);
  }

}
