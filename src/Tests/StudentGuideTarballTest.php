<?php

namespace Drupal\student_guide\Tests;

/**
 * Tests the config installer profile by uploading a tarball.
 *
 * @group StudentGuide
 */
class StudentGuideTarballTest extends StudentGuideTestBase {

  /**
   * {@inheritdoc}
   */
  protected function setUpSyncForm() {
    // Upload the tarball.
    $this->drupalPostForm(NULL, ['files[import_tarball]' => $this->getTarball()], 'Save and continue');
  }

}
