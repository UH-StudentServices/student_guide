<?php

namespace Drupal\uhsg_chat\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

class ChatConfigForm extends ConfigFormBase {

    /**
     * {@inheritdoc}
     */
    public function getFormId() {
      return 'uhsg_chat_config';
    }
  
    /**
     * {@inheritdoc}
     */
    protected function getEditableConfigNames() {
      return ['uhsg_chat.config'];
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state) {
      $chat_config = $this->config('uhsg_chat.config');
  
      $form['uhsg_chat'] = [
        '#type' => 'details',
        '#title' => t('Chat configuration'),
        '#description' => t('Note: Node IDs are configured programmatically or using drush!'),
        '#open' => TRUE,
      ];
      $form['uhsg_chat']['src'] = [
        '#type' => 'textfield',
        '#title' => t('Source URL'),
        '#default_value' => $chat_config->get('src'),
        '#required' => TRUE,
      ];
      $form['uhsg_chat']['key'] = [
        '#type' => 'textfield',
        '#title' => t('Key'),
        '#default_value' => $chat_config->get('key'),
        '#required' => TRUE,
      ];
      $form['uhsg_chat']['offsetX'] = [
        '#type' => 'textfield',
        '#title' => t('Offset X'),
        '#default_value' => $chat_config->get('offsetX'),
      ];
      $form['uhsg_chat']['agentNote'] = [
        '#type' => 'textfield',
        '#title' => t('Agent note'),
        '#default_value' => $chat_config->get('agentNote'),
      ];
      $form['uhsg_chat']['title'] = [
        '#type' => 'textfield',
        '#title' => t('Title'),
        '#default_value' => $chat_config->get('title'),
      ];
      $form['uhsg_chat']['infoTitle'] = [
        '#type' => 'textfield',
        '#title' => t('Info title'),
        '#default_value' => $chat_config->get('infoTitle'),
      ];
      $form['uhsg_chat']['infoDesc'] = [
        '#type' => 'textfield',
        '#title' => t('Info description'),
        '#default_value' => $chat_config->get('infoDesc'),
      ];
  
      return parent::buildForm($form, $form_state);
    }

    /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state) {
      $this->config('uhsg_chat.config')
        ->set('src', $form_state->getValue('src'))
        ->set('key', $form_state->getValue('key'))
        ->set('offsetX', $form_state->getValue('offsetX'))
        ->set('agentNote', $form_state->getValue('agentNote'))
        ->set('title', $form_state->getValue('title'))
        ->set('infoTitle', $form_state->getValue('infoTitle'))
        ->set('infoDesc', $form_state->getValue('infoDesc'))
        ->save();
  
      parent::submitForm($form, $form_state);
    }

}
