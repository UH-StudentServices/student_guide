<?php

use Drupal\Tests\UnitTestCase;
use Drupal\uhsg_oprek\Oprek\StudyRight\StudyRight;
use Drupal\Component\Serialization\Json;
use Drupal\uhsg_oprek\Oprek\StudyRight\StudyRightInterface;

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
      Json::decode(file_get_contents(__DIR__ . '/../study_rights_response.3.json')),
      Json::decode(file_get_contents(__DIR__ . '/../study_rights_response.4.json')),
    ];
  }

  /**
   * @test
   */
  public function getStateProperties() {
    $expectedStates = [0 => StudyRightInterface::STATE_PRIMARY, 1 => StudyRightInterface::STATE_OPTION];
    foreach ($expectedStates as $index => $expectedState) {
      $study_right = new StudyRight($this->studyRightDecodedResponses[0]['data'][$index]);
      $this->assertEquals($expectedState, $study_right->getState());
    }
  }

  /**
   * @test
   */
  public function getElementsProperties() {
    $expectedElementCounts = [0 => 4, 1 => 2];
    foreach ($expectedElementCounts as $index => $expectedElementCount) {
      $study_right = new StudyRight($this->studyRightDecodedResponses[0]['data'][$index]);
      $study_right->setDate(new \DateTime('2018-10-24T11:00:00.000Z'));
      $this->assertEquals($expectedElementCount, count($study_right->getElements()));
    }
  }

  /**
   * @test
   */
  public function getTargetingCodesProperties() {
    // See these exepcted values from $this->studyRightDecodedResponses
    $expectedTargetedCodes = [
      0 => [
        ['primary' => FALSE, 'code' => 'KH60_001'],
        ['primary' => FALSE, 'code' => 'SH60_039'],
        ['primary' => TRUE, 'code' => 'KH60_001SH60_039'],
      ],
      1 => [
        ['primary' => TRUE, 'code' => 'KH60_001'],
      ],
      2 => [
        ['primary' => TRUE, 'code' => 'SH60_039'],
      ],
      3 => [
        ['primary' => FALSE, 'code' => 'KH60_001'],
        ['primary' => FALSE, 'code' => 'SH60_039'],
        ['primary' => TRUE, 'code' => 'KH60_001SH60_039'],
      ],
      4 => [
        ['primary' => FALSE, 'code' => 'KH60_001'],
        ['primary' => FALSE, 'code' => 'SH60_039'],
        ['primary' => TRUE, 'code' => 'KH60_001SH60_039'],
      ],
    ];
    foreach ($expectedTargetedCodes as $study_right_index => $expected_study_right) {
      $study_right = new StudyRight($this->studyRightDecodedResponses[$study_right_index]['data'][0]);
      $targetedCodes = $study_right->getTargetedCodes();
      foreach ($expected_study_right as $index => $expectedTargetingCode) {
        $this->assertEquals($expectedTargetingCode['primary'], $targetedCodes[$index]->isPrimary());
        $this->assertEquals($expectedTargetingCode['code'], $targetedCodes[$index]->getCode());
      }
    }
  }

}
