<?php

/**
 * @file
 * Enables modules and site configuration for a minimal site installation.
 */

use Drupal\Core\Extension\ExtensionDiscovery;
use Drupal\Core\Config\StorageComparer;
use Drupal\Core\Config\ConfigImporter;
use Drupal\Core\Config\ConfigImporterException;
use Drupal\Core\Site\Settings;
use Drupal\student_guide\Storage\SourceStorage;
use Drupal\block_content\Entity\BlockContent;

/**
 * Need to do a manual include since this install profile never actually gets
 * installed so therefore its code cannot be autoloaded.
 */
include_once __DIR__ . '/src/Form/SiteConfigureForm.php';
include_once __DIR__ . '/src/Form/SyncConfigureForm.php';
include_once __DIR__ . '/src/Storage/SourceStorage.php';

/**
 * Implements hook_install_tasks_alter().
 */
function student_guide_install_tasks_alter(&$tasks, $install_state) {
  $key = array_search('install_profile_modules', array_keys($tasks));
  unset($tasks['install_profile_modules']);
  unset($tasks['install_profile_themes']);
  unset($tasks['install_install_profile']);
  $config_tasks = [
    'student_guide_upload' => [
      'display_name' => t('Upload config'),
      'type' => 'form',
      'function' => 'Drupal\student_guide\Form\SyncConfigureForm',
    ],
    'config_install_batch' => [
      'display_name' => t('Install configuration'),
      'type' => 'batch',
    ],
    'student_guide_fix_profile' => [],
  ];
  $tasks = array_slice($tasks, 0, $key, TRUE) +
    $config_tasks +
    array_slice($tasks, $key, NULL, TRUE);
  $tasks['install_configure_form']['function'] = 'Drupal\student_guide\Form\SiteConfigureForm';
}

/**
 * Creates a batch for the config importer to process.
 *
 * @see student_guide_install_tasks_alter()
 */
function config_install_batch() {
  // We need to manually trigger the installation of core-provided entity types,
  // as those will not be handled by the module installer.
  // @see install_profile_modules()
  install_core_entity_type_definitions();

  // Create a source storage that reads from sync.
  $listing = new ExtensionDiscovery(\Drupal::root());
  $listing->setProfileDirectories([]);
  $sync = new SourceStorage(\Drupal::service('config.storage.sync'), $listing->scan('profile'));

  // Match up the site uuids, the install_base_system install task will have
  // installed the system module and created a new UUID.
  $system_site = $sync->read('system.site');
  \Drupal::configFactory()->getEditable('system.site')->set('uuid', $system_site['uuid'])->save();

  // Create the storage comparer and the config importer.
  $config_manager = \Drupal::service('config.manager');
  $storage_comparer = new StorageComparer($sync, \Drupal::service('config.storage'), $config_manager);
  $storage_comparer->createChangelist();
  $config_importer = new ConfigImporter(
    $storage_comparer,
    \Drupal::service('event_dispatcher'),
    $config_manager,
    \Drupal::service('lock.persistent'),
    \Drupal::service('config.typed'),
    \Drupal::service('module_handler'),
    \Drupal::service('module_installer'),
    \Drupal::service('theme_handler'),
    \Drupal::service('string_translation')
  );

  try {
    $sync_steps = $config_importer->initialize();

    // Implementing hook_config_import_steps_alter() in this file does not work
    // if using the 'drush site-install' command. Add the step to fix the import
    // profile before the last step of the configuration import.
    $last = array_pop($sync_steps);
    $sync_steps[] = 'student_guide_install_uninstalled_profile_dependencies';
    $sync_steps[] = 'student_guide_config_import_profile';
    $sync_steps[] = $last;

    $batch = [
      'operations' => [],
      'finished' => 'config_install_batch_finish',
      'title' => t('Synchronizing configuration'),
      'init_message' => t('Starting configuration synchronization.'),
      'progress_message' => t('Completed @current step of @total.'),
      'error_message' => t('Configuration synchronization has encountered an error.'),
      'file' => drupal_get_path('module', 'config') . '/config.admin.inc',
    ];
    foreach ($sync_steps as $sync_step) {
      $batch['operations'][] = [
        'config_install_batch_process',
        [$config_importer, $sync_step],
      ];
    }

    return $batch;
  }
  catch (ConfigImporterException $e) {
    // There are validation errors.
    \Drupal::messenger()->addStatus(\Drupal::translation()->translate('The configuration synchronization failed validation.'));
    foreach ($config_importer->getErrors() as $message) {
      \Drupal::messenger()->addError($message);
    }
  }
}

/**
 * Processes the config import batch and persists the importer.
 *
 * @param \Drupal\Core\Config\ConfigImporter $config_importer
 *   The batch config importer object to persist.
 * @param string $sync_step
 *   The synchronisation step to do.
 * @param $context
 *   The batch context.
 *
 * @see config_install_batch()
 */
function config_install_batch_process(ConfigImporter $config_importer, $sync_step, &$context) {
  if (!isset($context['sandbox']['config_importer'])) {
    $context['sandbox']['config_importer'] = $config_importer;
  }

  $config_importer = $context['sandbox']['config_importer'];
  $config_importer->doSyncStep($sync_step, $context);
  if ($errors = $config_importer->getErrors()) {
    if (!isset($context['results']['errors'])) {
      $context['results']['errors'] = [];
    }
    $context['results']['errors'] += $errors;
  }
}

/**
 * Finish config importer batch.
 *
 * @see config_install_batch()
 */
function config_install_batch_finish($success, $results, $operations) {
  if ($success) {
    if (!empty($results['errors'])) {
      foreach ($results['errors'] as $error) {
        \Drupal::messenger()->addError($error);
        \Drupal::logger('config_sync')->error($error);
      }
      \Drupal::messenger()->addWarning(\Drupal::translation()->translate('The configuration was imported with errors.'));
    }
    else {
      // Configuration sync needs a complete cache flush.
      drupal_flush_all_caches();
    }
  }
  else {
    // An error occurred.
    // $operations contains the operations that remained unprocessed.
    $error_operation = reset($operations);
    $message = \Drupal::translation()
      ->translate('An error occurred while processing %error_operation with arguments: @arguments', [
        '%error_operation' => $error_operation[0],
        '@arguments' => print_r($error_operation[1], TRUE),
      ]);
    \Drupal::messenger()->addError($message);
  }
}

/**
 * Ensures all profile dependencies are created.
 *
 * @param array $context.
 *   The batch context.
 * @param \Drupal\Core\Config\ConfigImporter $config_importer
 *   The config importer.
 *
 * @see config_install_batch()
 */
function student_guide_install_uninstalled_profile_dependencies(array &$context, ConfigImporter $config_importer) {
  if (!array_key_exists('missing_profile_dependencies', $context)) {
    $profile = _student_guide_get_original_install_profile();
    $profile_file = drupal_get_path('profile', $profile) . "/$profile.info.yml";
    $info = \Drupal::service('info_parser')->parse($profile_file);
    $context['missing_profile_dependencies'] = array_diff($info['dependencies'], array_keys(\Drupal::moduleHandler()->getModuleList()));
    if (count($context['missing_profile_dependencies']) === 0) {
      $context['finished'] = 1;
      return;
    }
    // @todo Need to dependency sort them...
  }

  $still_missing = array_diff($context['missing_profile_dependencies'], array_keys(\Drupal::moduleHandler()->getModuleList()));
  if (!empty($still_missing)) {
    $missing_module = array_shift($still_missing);
    // This is not a config sync module install... fun!
    \Drupal::service('config.installer')->setSyncing(FALSE);
    \Drupal::service('module_installer')->install([$missing_module]);
    $context['message'] = t('Installed @module. This will be uninstalled after the profile has been installed.', ['@module' => $missing_module]);
  }
  $context['finished'] = (count($context['missing_profile_dependencies']) - count($still_missing)) / count($context['missing_profile_dependencies']);
}

/**
 * Processes profile as part of configuration sync.
 *
 * @param array $context.
 *   The batch context.
 * @param \Drupal\Core\Config\ConfigImporter $config_importer
 *   The config importer.
 *
 * @see config_install_batch()
 */
function student_guide_config_import_profile(array &$context, ConfigImporter $config_importer) {
  $orginal_profile = _student_guide_get_original_install_profile();
  if ($orginal_profile) {
    \Drupal::service('config.installer')
      ->setSyncing(TRUE)
      ->setSourceStorage($config_importer->getStorageComparer()->getSourceStorage());
    \Drupal::service('module_installer')->install([$orginal_profile], FALSE);
    module_set_weight($orginal_profile, 1000);
    $context['message'] = t('Synchronising install profile: @name.', ['@name' => $orginal_profile]);
  }
  $context['finished'] = 1;
}

/**
 * Fixes configuration if the install profile has made changes in hook_install().
 *
 * @see student_guide_install_tasks_alter()
 */
function student_guide_fix_profile() {
  global $install_state;
  // It is possible that installing the profile makes unintended configuration
  // changes.
  $config_manager = \Drupal::service('config.manager');
  $storage_comparer = new StorageComparer(\Drupal::service('config.storage.sync'), \Drupal::service('config.storage'), $config_manager);
  $storage_comparer->createChangelist();
  if ($storage_comparer->hasChanges()) {
    // Swap out the install profile so that the profile module exists.
    _student_guide_switch_profile(_student_guide_get_original_install_profile());
    \Drupal::service('extension.list.profile')->reset();
    \Drupal::service('extension.list.module')->reset();
    \Drupal::service('extension.list.theme_engine')->reset();
    \Drupal::service('extension.list.theme')->reset();
    $config_importer = new ConfigImporter(
      $storage_comparer,
      \Drupal::service('event_dispatcher'),
      $config_manager,
      \Drupal::service('lock.persistent'),
      \Drupal::service('config.typed'),
      \Drupal::service('module_handler'),
      \Drupal::service('module_installer'),
      \Drupal::service('theme_handler'),
      \Drupal::service('string_translation')
    );
    try {
      $config_importer->import();
    }
    catch (ConfigImporterException $e) {
      // There are validation errors.
      \Drupal::messenger()->addStatus(\Drupal::translation()->translate('The configuration synchronization failed validation.'));
      foreach ($config_importer->getErrors() as $message) {
        \Drupal::messenger()->addError($message);
      }
    }
    // Replace the install profile so that the student_guide still works.
    _student_guide_switch_profile('student_guide');
    \Drupal::service('extension.list.profile')->reset();
    \Drupal::service('extension.list.module')->reset();
    \Drupal::service('extension.list.theme_engine')->reset();
    \Drupal::service('extension.list.theme')->reset();
  }
}

/**
 * Switch the currently active profile in the installer.
 *
 * @param string $profile
 *   The profile to switch to.
 */
function _student_guide_switch_profile($profile) {
  global $install_state;
  $install_state['parameters']['profile'] = $profile;
  $settings = Settings::getAll();
  $settings['install_profile'] = $profile;
  new Settings($settings);
}

/**
 * Gets the original install profile name.
 *
 * @return string|null
 *   The name of the install profile from the sync configuration.
 */
function _student_guide_get_original_install_profile() {
  $original_profile = NULL;
  // Profiles need to be extracted from the install list if they are there.
  // This is because profiles need to be installed after all the configuration
  // has been processed.
  $listing = new ExtensionDiscovery(\Drupal::root());
  $listing->setProfileDirectories([]);
  // Read directly from disk since the source storage in the config importer is
  // being altered to exclude profiles.
  $new_extensions = \Drupal::service('config.storage.sync')
    ->read('core.extension')['module'];
  $profiles = array_intersect_key($listing->scan('profile'), $new_extensions);

  if (!empty($profiles)) {
    // There can be only one.
    $original_profile = key($profiles);
  }
  return $original_profile;
}

/**
 * Create content block for front page text (Instructions for students).
 */
function student_guide_create_front_page_text_block() {
  $text_fi = 'Opas antaa sinulle kaiken opinnoissa tarvitsemasi tiedon. Voit hakea tietoa joko vapaan haun tai valmiiksi koostettujen teemojen avulla.';
  $text_sv = 'Add swedish translation';
  $text_en = 'Add english translation';

  // get block uuid from config, see https://www.drupal.org/node/2756331
  $plugin = \Drupal::config('block.block.frontpagetextblock')->get('plugin');
  $uuid = substr($plugin, strpos($plugin, ':') + 1);

  $block = BlockContent::create([
    'info' => 'Front page text block',
    'type' => 'content_block',
    'langcode' => 'fi',
    'uuid' => $uuid,
  ]);

  $block->set('field_content_block_text', $text_fi);
  $block->addTranslation('sv', ['field_content_block_text' => $text_sv]);
  $block->addTranslation('en', ['field_content_block_text' => $text_en]);
  $block->save();
}

/**
 * Create content block for front page additional text (Instructions for
 * students).
 */
function student_guide_create_front_page_additional_text_block() {
  $block = BlockContent::create([
    'info' => 'Front page additional text block',
    'type' => 'content_block',
    'langcode' => 'fi',
    'uuid' => 'ecd71681-cd04-4ee0-b9b2-015cc2a19ff0',
  ]);

  $block->set('field_content_block_text', '');
  $block->addTranslation('sv', ['field_content_block_text' => '']);
  $block->addTranslation('en', ['field_content_block_text' => '']);
  $block->save();
}

/**
 * Create content block for front page text (Instructions for teaching).
 */
function student_guide_create_teaching_front_page_text_block() {
  $block = BlockContent::create([
    'info' => 'Front page text block: Teaching',
    'type' => 'content_block',
    'langcode' => 'fi',
    'uuid' => '46c7c03f-88cb-42bd-ab54-e8ac5cce8bab',
  ]);

  $block->set('field_content_block_text', '');
  $block->addTranslation('sv', ['field_content_block_text' => '']);
  $block->addTranslation('en', ['field_content_block_text' => '']);
  $block->save();
}


/**
 * Create content block for front page additional text (Instructions for
 * teaching).
 */
function student_guide_create_teaching_front_page_additional_text_block() {
  $block = BlockContent::create([
    'info' => 'Front page additional text block: Teaching',
    'type' => 'content_block',
    'langcode' => 'fi',
    'uuid' => '56a05a0a-10f0-4c28-bd26-63e5e2e82768',
  ]);

  $block->set('field_content_block_text', '');
  $block->addTranslation('sv', ['field_content_block_text' => '']);
  $block->addTranslation('en', ['field_content_block_text' => '']);
  $block->save();
}

/**
 * Create content block for Degree programme selector description text
 * (Instructions for students).
 */
function student_guide_create_students_degree_programme_selection_description_text_block() {
  $block = BlockContent::create([
    'info' => 'Degree programme selector description text block: Students',
    'type' => 'content_block',
    'langcode' => 'fi',
    'uuid' => 'd95bab6e-3e46-4c10-ba3e-fcb69254b11d',
  ]);

  $block->set('field_content_block_text', '');
  $block->addTranslation('sv', ['field_content_block_text' => '']);
  $block->addTranslation('en', ['field_content_block_text' => '']);
  $block->save();
}

/**
 * Create content block for Degree programme selector description text
 * (Instructions for teaching).
 */
function student_guide_create_teaching_degree_programme_selection_description_text_block() {
  $block = BlockContent::create([
    'info' => 'Degree programme selector description text block: Teaching',
    'type' => 'content_block',
    'langcode' => 'fi',
    'uuid' => '572ed55b-455d-4d67-b726-7905452987e4',
  ]);

  $block->set('field_content_block_text', '');
  $block->addTranslation('sv', ['field_content_block_text' => '']);
  $block->addTranslation('en', ['field_content_block_text' => '']);
  $block->save();
}
