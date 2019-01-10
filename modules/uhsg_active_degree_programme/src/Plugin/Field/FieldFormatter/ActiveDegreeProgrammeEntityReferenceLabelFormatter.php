<?php

namespace Drupal\uhsg_active_degree_programme\Plugin\Field\FieldFormatter;

use Drupal\Core\Entity\Exception\UndefinedLinkTemplateException;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\Plugin\Field\FieldFormatter\EntityReferenceLabelFormatter;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\uhsg_active_degree_programme\ActiveDegreeProgrammeService;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation of the 'entity reference label' formatter.
 *
 * @FieldFormatter(
 *   id = "active_degree_programme_entity_reference_label",
 *   label = @Translation("Active degree programme label"),
 *   description = @Translation("Display the label of the referenced entities in a condensed format."),
 *   field_types = {
 *     "entity_reference"
 *   }
 * )
 */
class ActiveDegreeProgrammeEntityReferenceLabelFormatter extends EntityReferenceLabelFormatter implements ContainerFactoryPluginInterface {

  /**
   * @var \Drupal\uhsg_active_degree_programme\ActiveDegreeProgrammeService
   */
  protected $activeDegreeProgrammeService;

  /**
   * Constructs a new instance of the plugin.
   *
   * @param string $plugin_id
   *   The plugin_id for the formatter.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   The definition of the field to which the formatter is associated.
   * @param array $settings
   *   The formatter settings.
   * @param string $label
   *   The formatter label display setting.
   * @param string $view_mode
   *   The view mode.
   * @param array $third_party_settings
   *   Any third party settings.
   * @param \Drupal\uhsg_active_degree_programme\ActiveDegreeProgrammeService $activeDegreeProgrammeService
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, $label, $view_mode, array $third_party_settings, ActiveDegreeProgrammeService $activeDegreeProgrammeService) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $label, $view_mode, $third_party_settings);
    $this->activeDegreeProgrammeService = $activeDegreeProgrammeService;
  }

  /**
   * Creates an instance of the plugin.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   The container to pull out services used in the plugin.
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   *
   * @return static
   *   Returns an instance of this plugin.
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['label'],
      $configuration['view_mode'],
      $configuration['third_party_settings'],
      $container->get('uhsg_active_degree_programme.active_degree_programme')
    );
  }

  /**
   * {@inheritdoc}
   *
   * Return a condensed representation of the elements if one of the elements
   * matches the active degree programme. Otherwise use default implementation.
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    if ($activeDegreeProgrammeId = $this->activeDegreeProgrammeService->getId()) {
      return $this->viewCondensedElements($items, $langcode, $activeDegreeProgrammeId);
    }

    return parent::viewElements($items, $langcode);
  }

  /**
   * @see viewElements
   */
  protected function viewCondensedElements(FieldItemListInterface $items, $langcode, $activeDegreeProgrammeId) {
    $elements = [];
    $output_as_link = $this->getSetting('link');
    $entitiesToView = $this->getEntitiesToView($items, $langcode);

    foreach ($entitiesToView as $delta => $entity) {
      if ($entity->id() === $activeDegreeProgrammeId) {
        $label = $this->getCondensedLabel($entity, $entitiesToView);
        // If the link is to be displayed and the entity has a uri, display a
        // link.
        if ($output_as_link && !$entity->isNew()) {
          try {
            $uri = $entity->urlInfo();
          }
          catch (UndefinedLinkTemplateException $e) {
            // This exception is thrown by \Drupal\Core\Entity\Entity::urlInfo()
            // and it means that the entity type doesn't have a link template nor
            // a valid "uri_callback", so don't bother trying to output a link for
            // the rest of the referenced entities.
            $output_as_link = FALSE;
          }
        }

        if ($output_as_link && isset($uri) && !$entity->isNew()) {
          $elements[$delta] = [
            '#type' => 'link',
            '#title' => $label,
            '#url' => $uri,
            '#options' => $uri->getOptions(),
          ];

          if (!empty($items[$delta]->_attributes)) {
            $elements[$delta]['#options'] += ['attributes' => []];
            $elements[$delta]['#options']['attributes'] += $items[$delta]->_attributes;
            // Unset field item attributes since they have been included in the
            // formatter output and shouldn't be rendered in the field template.
            unset($items[$delta]->_attributes);
          }
        }
        else {
          $elements[$delta] = ['#plain_text' => $label];
        }
        $elements[$delta]['#cache']['tags'] = $entity->getCacheTags();
      }
    }

    return $elements;
  }

  protected function getCondensedLabel($entity, $entitiesToView) {
    return $entity->label() . ' ' . $this->t('and @count others', ['@count' => count($entitiesToView) - 1]);
  }
}
