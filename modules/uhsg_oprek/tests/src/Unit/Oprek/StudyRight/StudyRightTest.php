<?php

use Drupal\Tests\UnitTestCase;
use Drupal\uhsg_oprek\Oprek\StudyRight\StudyRight;
use Drupal\Component\Serialization\Json;

/**
 * @group uhsg
 */
class StudyRightTest extends UnitTestCase {

  /** @var []object*/
  private $studyRightDecodedResponses;

  public function setUp() {
    parent::setUp();
    $this->studyRightDecodedResponses = [
      Json::decode(file_get_contents(__DIR__ . '/../study_rights_response.0.json')),
      Json::decode(file_get_contents(__DIR__ . '/../study_rights_response.1.json')),
      Json::decode(file_get_contents(__DIR__ . '/../study_rights_response.2.json')),
    ];
  }

  /**
   * @test
   */
  public function getStateProperties() {
    $expectedStates = [0 => StudyRight::STATE_PRIMARY, 1 => StudyRight::STATE_OPTION];
    foreach ($expectedStates as $index => $expectedState) {
      $study_right = new StudyRight($this->studyRightDecodedResponses[0]['data'][$index]);
      $this->assertEquals($study_right->getState(), $expectedState);
    }
  }

  /**
   * @test
   */
  public function getElementsProperties() {
    $expectedElementCounts = [0 => 6, 1 => 5];
    foreach ($expectedElementCounts as $index => $expectedElementCount) {
      $study_right = new StudyRight($this->studyRightDecodedResponses[0]['data'][$index]);
      $this->assertEquals(count($study_right->getElements()), $expectedElementCount);
    }
  }

  /**
   * @test
   */
  public function getTargetingCodesProperties() {
    $expectedTargetingCodes = [
      0 => [
        ['primary' => FALSE, 'code' => 'KH60_001'],
        ['primary' => TRUE, 'code' => 'KH60_001SH60_039'],
        ['primary' => FALSE, 'code' => 'SH60_039'],
      ],
      1 => [
        ['primary' => TRUE, 'code' => 'KH60_001'],
      ],
      2 => [
        ['primary' => TRUE, 'code' => 'SH60_039'],
      ],
    ];
    foreach ($expectedTargetingCodes as $study_right_index => $expectedTargetingCode) {
      $study_right = new StudyRight($this->studyRightDecodedResponses[$study_right_index]['data'][0]);
      foreach ($expectedTargetingCodes as $index => $expectedTargetingCode) {
        foreach ($study_right->getTargetingCodes() as $targetingCode) {
          $this->assertEquals($targetingCode['primary'], $targetingCode->isPrimary());
          $this->assertEquals($targetingCode['code'], $targetingCode->getCode());
        }
      }
    }
  }

}
