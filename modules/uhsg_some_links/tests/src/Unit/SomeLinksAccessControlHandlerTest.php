<?php

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\uhsg_some_links\SomeLinksAccessControlHandler;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @group uhsg
 */
class SomeLinksAccessControlHandlerTest extends PHPUnit_Framework_TestCase {

  /** @var AccountInterface */
  private $account;

  /** @var ContainerInterface */
  private $container;

  /** @var EntityInterface */
  private $entity;

  /** @var EntityTypeInterface */
  private $entityType;

  /** @var LanguageInterface */
  private $language;

  /** @var SomeLinksAccessControlHandler */
  private $someLinksAccessControlHandler;

  public function setUp() {
    parent::setUp();

    $this->account = $this->prophesize(AccountInterface::class);
    $this->container = $this->prophesize(ContainerInterface::class);
    $this->language = $this->prophesize(LanguageInterface::class);

    $this->entity = $this->prophesize(EntityInterface::class);
    $this->entity->language()->willReturn($this->language);
    $this->entity->uuid()->willReturn('uuid');

    $this->entityType = $this->prophesize(EntityTypeInterface::class);
    $this->someLinksAccessControlHandler = new SomeLinksAccessControlHandler($this->entityType->reveal());

    Drupal::setContainer($this->container->reveal());
  }

  /**
   * @test
   */
  public function shouldReturnNeutralAccessResultOnUknownOperation() {
    $this->someLinksAccessControlHandler->access($this->entity->reveal(), 'unknown operation', $this->account->reveal());
  }
}