<?php

namespace Drupal\uhsg_degree_programme\Flag;

use Drupal\Core\Access\AccessResultForbidden;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\flag\FlagInterface;
use Drupal\flag\FlagServiceInterface;
use Drupal\flag\Plugin\Flag\EntityFlagType;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * This class overrides EntityFlagType so that we can override the actionAccess
 * to follow business logic we want.
 */
class MyDegreeProgrammeFlagType extends EntityFlagType {

  /**
   * Specify the flag id where we want to apply this specific business logic.
   * @var string
   */
  protected $applyCustomAccessLogicId = 'my_degree_programmes';

  /**
   * @var \Drupal\flag\FlagServiceInterface
   */
  protected $flagService;

  /**
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected $config;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, array $plugin_definition, ModuleHandlerInterface $module_handler, EntityTypeManagerInterface $entity_type_manager, FlagServiceInterface $flagService, ConfigFactoryInterface $configFactory) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $module_handler, $entity_type_manager);
    $this->flagService = $flagService;
    $this->config = $configFactory->get('uhsg_degree_programme.settings');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('module_handler'),
      $container->get('entity_type.manager'),
      $container->get('flag'),
      $container->get('config.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function actionAccess($action, FlagInterface $flag, AccountInterface $account, EntityInterface $flaggable = NULL) {
    if ($account->isAnonymous()) {
      return new AccessResultForbidden();
    }

    $access = parent::actionAccess($action, $flag, $account, $flaggable);

    // When specified flag type, then introduce some additional access logic
    if ($flag->id() == $this->applyCustomAccessLogicId && !is_null($flaggable)) {
      // Get the flagging and deny access if the flagging has been created
      // programmatically.
      if ($flagging = $this->flagService->getFlagging($flag, $flaggable, $account)) {
        $technical_condition_field_name = $this->config->get('technical_condition_field_name');
        if ($flagging->hasField($technical_condition_field_name) &&
            !$flagging->get($technical_condition_field_name)->isEmpty() &&
            $flagging->get($technical_condition_field_name)->first()->getValue()['value']) {
          $access = $access->andIf(new AccessResultForbidden());
        }
      }
    }

    return $access;
  }

}
