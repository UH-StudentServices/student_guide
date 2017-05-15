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
    $available_roles = array_keys(user_roles(TRUE));

    $available_roles = array_filter($available_roles, function($role) {
      return $role != 'authenticated';
    });

    $formDescription = $this->t(
      'Specified roles are given to the members of the IAM groups listed below. If you want to add a new group, search for the group at the <a href=":url" target="_blank">group administration tool</a>.',
      [':url' => 'https://idm.helsinki.fi/web/groupadmin/group_search.htm?searchgroup=searchgroup']
    );

    $form['description'] = [
      '#type' => 'item',
      '#description' => $formDescription
    ];

    $mappingFormatDescription = $this->t(
      'Write each definition into its own line. Definitions are set in following format: <code>grp-doo-myteam content_editor</code>. Available roles are: @roles',
      ['@roles' => implode(', ', $available_roles)]
    );

    $form[self::GROUP_TO_ROLES] = [
      '#type' => 'textarea',
      '#title' => $this->t('Group to role mapping'),
      '#default_value' => $this->getMappingAsPlainText(),
      '#description' => $mappingFormatDescription,
      '#placeholder' => 'grp-doo-myteam content_editor',
      '#maxlength' => 256,
    ];

    return parent::buildForm($form, $form_state);
  }

  private function getMappingAsPlainText() {
    $groupToRoleMapping = $this->config(self::EDITABLE_CONFIG_NAME)->get(self::GROUP_TO_ROLES);
    $groupToRoleMapping = empty($groupToRoleMapping) ? [] : $groupToRoleMapping;

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
    $this->validateRoleMapping($form_state, $groupToRoleMapping);
    if (empty($form_state->getErrors())) {
      $groupToRoleMapping = $this->getGroupToRoleAsStructured($groupToRoleMapping);
      $this->validateRoles($form_state, array_column($groupToRoleMapping, self::RID));
    }
  }

  private function validateRoleMapping(FormStateInterface $form_state, $groupToRolesLines) {
    // Loop each line and ensure it has two values separated by delimiter
    foreach ($groupToRolesLines as $groupToRolesLine) {
      $groupsRolesParts = explode(' ', $groupToRolesLine);
      // The validation: Ensure we have two parts in the line
      if (count($groupsRolesParts) != 2) {
        $form_state->setErrorByName(self::GROUP_TO_ROLES, $this->t('Each line must have two values separated whitespace.'));
      }
    }
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
    $groupToRoleMapping = $this->getGroupToRoleAsStructured($groupToRoleMapping);

    $this->config(self::EDITABLE_CONFIG_NAME)
      ->set(self::GROUP_TO_ROLES, $groupToRoleMapping)
      ->save();

    parent::submitForm($form, $form_state);
  }

  private function getGroupToRoleMapping(FormStateInterface $form_state) {
    $groupToRolesText = $form_state->getValue(self::GROUP_TO_ROLES);
    $groupToRolesText = str_replace("\r\n", "\n", $groupToRolesText);
    $groupToRolesLines = array_map('trim', explode("\n", $groupToRolesText));
    $groupToRolesLines = array_filter($groupToRolesLines, function ($line) {
      return !empty(trim($line));
    });

    return $groupToRolesLines;
  }

  private function getGroupToRoleAsStructured($groupToRolesLines) {

    $groupToRoleMapping = array_map(function ($groupToRolesLine) {
      $groupsRolesParts = explode(' ', $groupToRolesLine);
      $group = $groupsRolesParts[0];
      $role = $groupsRolesParts[1];

      return [self::GROUP_NAME => $group, self::RID => $role];
    }, $groupToRolesLines);

    return $groupToRoleMapping;
  }
}
