<?php

namespace Drupal\student_guide\Tests;

use Drupal\Core\Archiver\ArchiveTar;
use Drupal\Core\Config\FileStorage;
use Drupal\Core\Site\Settings;
use Drupal\user\UserInterface;
use Drupal\Core\File\FileSystem;

/**
 * Tests the config installer profile by having files in a sync directory.
 *
 * @group StudentGuide
 */
class StudentGuideSyncTest extends StudentGuideTestBase {

  /**
   * The directory where the configuration to install is stored.
   *
   * @var string
   */
  protected $syncDir;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    $this->syncDir = 'public://' . $this->randomMachineName(128);
    parent::setUp();
  }

  /**
   * Ensures that the user page is available after installation.
   */
  public function testInstaller() {
    // Do assertions from parent.
    parent::testInstaller();

    // Do assertions specific to test.
    $this->assertEqual(\Drupal::service('file_system')->realpath($this->syncDir), Settings::get('config_sync_directory'), 'The sync directory has been updated during the installation.');
    $this->assertEqual(UserInterface::REGISTER_ADMINISTRATORS_ONLY, \Drupal::config('user.settings')->get('register'), 'Ensure standard_install() does not overwrite user.settings::register.');
    $this->assertEqual([], \Drupal::entityDefinitionUpdateManager()->getChangeSummary(), 'There are no entity or field definition updates.');
  }

  /**
   * {@inheritdoc}
   */
  protected function setUpSyncForm() {
    // Create a new sync directory.
    FileSystem::mkdir($this->syncDir);

    // Extract the tarball into the sync directory.
    $archiver = new ArchiveTar($this->getTarball(), 'gz');
    $files = [];
    foreach ($archiver->listContent() as $file) {
      $files[] = $file['filename'];
    }
    $archiver->extractList($files, $this->syncDir);

    // Change the user.settings::register so that we can test that
    // standard_install() does not override it.
    $sync = new FileStorage($this->syncDir);
    $user_settings = $sync->read('user.settings');
    $user_settings['register'] = UserInterface::REGISTER_ADMINISTRATORS_ONLY;
    $sync->write('user.settings', $user_settings);

    $this->drupalPostForm(NULL, ['sync_directory' => \Drupal::service('file_system')->realpath($this->syncDir)], 'Save and continue');
  }

}
