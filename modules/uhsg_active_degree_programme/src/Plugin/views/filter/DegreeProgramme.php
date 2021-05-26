<?php

namespace Drupal\uhsg_active_degree_programme\Plugin\views\filter;

use Drupal\Core\Form\FormStateInterface;
use Drupal\uhsg_active_degree_programme\ActiveDegreeProgrammeService;
use Drupal\views\Plugin\views\filter\FilterPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * The degree programme filter handler.
 *
 * @ingroup views_filter_handlers
 *
 * @ViewsFilter("degree_programme_views_filter")
 */
class DegreeProgramme extends FilterPluginBase {

  /**
   * This filter is always considered single-valued.
   *
   * @var bool
   */
  protected $alwaysMultiple = FALSE;

  /**
   * Active degree programme service.
   * 
   * @var \Drupal\uhsg_active_degree_programme\ActiveDegreeProgrammeService
   */
  protected $activeDegreeProgrammeService;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ActiveDegreeProgrammeService $activeDegreeProgrammeService) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->activeDegreeProgrammeService = $activeDegreeProgrammeService;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('uhsg_active_degree_programme.active_degree_programme')
    );
  }

  /**
   * The form that is shown (including the exposed form).
   */
  public function canExpose() {
    return (bool) $this->activeDegreeProgrammeService->getId();
  }

  /**
   * {@inheritdoc}
   */
  protected function valueForm(&$form, FormStateInterface $form_state) {
    $form['value'] = [
      '#type' => 'select',
      '#title' => $this->t('Limit by news type'),
      '#options' => [
        'all' => $this->t('All'),
        'general' => $this->t('General bulletins'),
        'degree' => $this->t('Degree programme bulletins'),
      ],
      '#default_value' => !empty($this->value) ? $this->value : 'all',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function query() {
    $this->ensureMyTable();
    $field_name = "$this->tableAlias.$this->realField";

    // Get active degree programme.
    $active_degree_id = $this->activeDegreeProgrammeService->getId();

    // Get selected type. Use 'all' by default and 'general' if there's
    // no active degree programme selected.
    $value = $this->value[0] ?? 'all';
    $value = $active_degree_id ? $value : 'general';

    // Filter results based on news type selection.
    switch ($value) {
      case 'all':
        $this->query
          ->addWhere($this->options['group'], db_or()
          ->condition($field_name, 'NULL', 'IS NULL')
          ->condition($field_name, $active_degree_id, '=')); 
        break;

      case 'general':
        $this->query
          ->addWhere($this->options['group'], $field_name, 'NULL', 'IS NULL');
        break;

      case 'degree':
        $this->query
          ->addWhere($this->options['group'], $field_name, $active_degree_id, '=');
        break;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    $contexts = parent::getCacheContexts();

    $contexts[] = 'url';
    $contexts[] = 'active_degree_programme';

    return $contexts;
  }

}
