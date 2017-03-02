<?php

namespace Drupal\uhsg_role_auto_assign\SamlAuth;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\user\Entity\Role;

class UserSyncForm extends ConfigFormBase {

  const EDITABLE_CONFIG_NAME = 'uhsg_role_auto_assign.settings';
  const GROUP_NAME = 'group_name';
  const GROUP_TO_ROLES = 'group_to_roles';
  const RID = 'rid';

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'uhsg_role_auto_assign_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [self::EDITABLE_CONFIG_NAME];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form[self::GROUP_TO_ROLES] = [
      '#type' => 'textarea',
      '#title' => $this->t('Group to role mapping'),
      '#default_value' => $this->getMappingAsPlainText(),
      '#maxlength' => 256,
    ];

    return parent::buildForm($form, $form_state);
  }

  private function getMappingAsPlainText() {
    $groupToRoleMapping = $this->config(self::EDITABLE_CONFIG_NAME)->get(self::GROUP_TO_ROLES);

    $mappingAsText = array_map(function ($mapping) {
      return "{$mapping[self::GROUP_NAME]} {$mapping[self::RID]}\n";
    }, $groupToRoleMapping);

    return trim(implode('', $mappingAsText));
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $groupToRoleMapping = $this->getGroupToRoleMapping($form_state);
    $this->validateRoles($form_state, array_column($groupToRoleMapping, self::RID));
  }

  private function validateRoles(FormStateInterface $form_state, array $rids) {
    foreach ($rids as $rid) {
      if (empty(Role::load($rid))) {
        $error = $this->t('Role by ID "@rid" not found.', ['@rid' => $rid]);
        $form_state->setErrorByName(self::GROUP_TO_ROLES, $error);
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $groupToRoleMapping = $this->getGroupToRoleMapping($form_state);

    $this->config(self::EDITABLE_CONFIG_NAME)
      ->set(self::GROUP_TO_ROLES, $groupToRoleMapping)
      ->save();

    parent::submitForm($form, $form_state);
  }

  private function getGroupToRoleMapping(FormStateInterface $form_state) {
    $groupToRolesText = $form_state->getValue(self::GROUP_TO_ROLES);
    $groupToRolesLines = array_map('trim', explode("\n", $groupToRolesText));

    $groupToRolesLines = array_filter($groupToRolesLines, function ($line) {
      return !empty(trim($line));
    });

    $groupToRoleMapping = array_map(function ($groupToRolesLine) {
      $groupsRolesParts = explode(' ', $groupToRolesLine);
      $group = $groupsRolesParts[0];
      $role = $groupsRolesParts[1];

      return [self::GROUP_NAME => $group, self::RID => $role];
    }, $groupToRolesLines);

    return $groupToRoleMapping;
  }
}
