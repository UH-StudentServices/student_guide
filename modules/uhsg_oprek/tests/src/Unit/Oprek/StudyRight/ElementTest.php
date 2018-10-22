<?php

use Drupal\Tests\UnitTestCase;
use Drupal\uhsg_oprek\Oprek\StudyRight\Element;
use Drupal\Component\Serialization\Json;

/**
 * @group uhsg
 */
class ElementTest extends UnitTestCase {

  const CODE_KEY = 'code';
  const CODE_VALUE = 'code value';

  /** @var \Drupal\uhsg_oprek\Oprek\StudyRight\Element*/
  private $element;

  /** @var object*/
  private $studyRightDecodedResponse;

  public function setUp() {
    parent::setUp();
    $this->studyRightDecodedResponse = Json::decode(file_get_contents(__DIR__ . '/../study_rights_response.0.json'));
  }

  /**
   * @test
   */
  public function getCodeShouldReturnEmptyStringWhenThereAreNoProperties() {
    $this->element = new Element([]);

    $this->assertEmpty($this->element->getCode());
  }

  /**
   * @test
   */
  public function getCodeShouldReturnCodePropertyExists() {
    $this->element = new Element([self::CODE_KEY => self::CODE_VALUE]);

    $this->assertEquals(self::CODE_VALUE, $this->element->getCode());
  }

  /**
   * @test
   */
  public function getCodeShouldReturnEmptyStringWhenCodePropertyDoesNotExist() {
    $this->element = new Element(['unknownKey' => 'unknownValue']);

    $this->assertEmpty($this->element->getCode());
  }

  /**
   * @test
   */
  public function getCodeShouldReturnCorrectCodes() {
    $expectedCodes = [
      0 => [
        0 => '00850',
        1 => 'A2004',
        2 => '620009',
        3 => 'KH60_001',
        4 => 'SH60_039',
        5 => '03417',
      ],
      1 => [
        0 => '00337',
        1 => 'YLTUTK',
        2 => 'A2004',
        3 => '620009',
        4 => '03417',
      ],
    ];
    foreach ($expectedCodes as $study_right_index => $elements) {
      foreach ($elements as $element_index => $expectedCode) {
        $element = new Element($this->studyRightDecodedResponse['data'][$study_right_index]['elements'][$element_index]);
        $this->assertEquals($element->getCode(), $expectedCode);
      }
    }
  }

  /**
   * @test
   */
  public function getIdShouldReturnCorrectIds() {
    $expectedIds = [
      0 => [
        0 => 10,
        1 => 15,
        2 => 20,
        3 => 20,
        4 => 30,
        5 => 40,
      ],
      1 => [
        0 => 10,
        1 => 10,
        2 => 15,
        3 => 20,
        4 => 40,
      ],
    ];
    foreach ($expectedIds as $study_right_index => $elements) {
      foreach ($elements as $element_index => $expectedId) {
        $element = new Element($this->studyRightDecodedResponse['data'][$study_right_index]['elements'][$element_index]);
        $this->assertEquals($element->getId(), $expectedId);
      }
    }
  }

}
