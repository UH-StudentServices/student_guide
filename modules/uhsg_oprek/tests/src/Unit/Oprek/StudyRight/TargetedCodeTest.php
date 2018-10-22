<?php

use Drupal\Tests\UnitTestCase;
use Drupal\Component\Serialization\Json;
use Drupal\uhsg_oprek\Oprek\StudyRight\Element;
use Drupal\uhsg_oprek\Oprek\StudyRight\TargetedCode;

/**
 * @group uhsg
 */
class TargetedCodeTest extends UnitTestCase {

  /** @var object*/
  private $studyRightDecodedResponse;

  /** @var array*/
  private $input;

  public function setUp() {
    parent::setUp();
    $this->studyRightDecodedResponse = Json::decode(file_get_contents(__DIR__ . '/../study_rights_response.0.json'));
    $this->input = [
      0 => [
        'primary' => TRUE,
        'elements' => [new Element(['code' => 'Z', 'element_id' => 20])],
      ],
      1 => [
        'primary' => TRUE,
        'elements' => [
          new Element(['code' => 'Z', 'element_id' => 20]),
          new Element(['code' => 'XY', 'element_id' => 30]),
        ],
      ],
      2 => [
        'primary' => TRUE,
        'elements' => [new Element(['code' => 'XY', 'element_id' => 30])],
      ],
      3 => [
        'primary' => FALSE,
        'elements' => [new Element(['code' => 'Z', 'element_id' => 20])],
      ],
      4 => [
        'primary' => FALSE,
        'elements' => [
          new Element(['code' => 'Z', 'element_id' => 20]),
          new Element(['code' => 'XY', 'element_id' => 30]),
        ],
      ],
      5 => [
        'primary' => FALSE,
        'elements' => [new Element(['code' => 'XY', 'element_id' => 30])],
      ],
    ];
  }

  /**
   * @test
   */
  public function isPrimaryShouldReturnCorrectPrimaryBooleans() {
    $expectedPrimaries = [
      0 => TRUE,
      1 => TRUE,
      2 => TRUE,
      3 => FALSE,
      4 => FALSE,
      5 => FALSE,
    ];
    foreach ($expectedPrimaries as $index => $expectedPrimary) {
      $targetedCode = new TargetedCode();

      // isPrimary() should return FALSE, because it hasn't been set
      $this->assertEquals($targetedCode->isPrimary(), FALSE);

      // isPrimary() should return expected primary boolean, because it has been
      // set with setPrimary()
      $targetedCode->setPrimary($this->input[$index]['primary']);
      $this->assertEquals($targetedCode->isPrimary(), $expectedPrimary);

      // Setting elements should not affect isPrimary()
      $targetedCode->setElements($this->input[$index]['elements']);
      $this->assertEquals($targetedCode->isPrimary(), $expectedPrimary);
    }
  }

  /**
   * @test
   */
  public function getCodeShouldReturnCorrectCodes() {
    $expectedCodes = [
      0 => 'Z',
      1 => 'ZXY',
      2 => 'XY',
      3 => 'Z',
      4 => 'ZXY',
      5 => 'XY',
    ];
    foreach ($expectedCodes as $index => $expectedCode) {
      $targetedCode = new TargetedCode();

      // getCode should return NULL, because it hasn't been set
      $this->assertEquals($targetedCode->getCode(), NULL);

      // getCode() should return exepcted codes, because they've been set with
      // setElements()
      $targetedCode->setElements($this->input[$index]['elements']);
      $this->assertEquals($targetedCode->getCode(), $expectedCode);

      // Setting primary should not effect on getCode()
      $targetedCode->setPrimary($this->input[$index]['primary']);
      $this->assertEquals($targetedCode->getCode(), $expectedCode);
    }
  }

}
