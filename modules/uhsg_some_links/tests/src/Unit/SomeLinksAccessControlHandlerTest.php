<?php

use Drupal\Core\Cache\Context\CacheContextsManager;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\uhsg_some_links\SomeLinksAccessControlHandler;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Prophecy\Argument;

/**
 * @group uhsg
 */
class SomeLinksAccessControlHandlerTest extends PHPUnit_Framework_TestCase {

  /** @var AccountInterface */
  private $account;

  /** @var CacheContextsManager */
  private $cacheContextsManager;

  /** @var ContainerInterface */
  private $container;

  /** @var EntityInterface */
  private $entity;

  /** @var EntityTypeInterface */
  private $entityType;

  /** @var LanguageInterface */
  private $language;

  /** @var ModuleHandlerInterface */
  private $moduleHandler;

  /** @var SomeLinksAccessControlHandler */
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