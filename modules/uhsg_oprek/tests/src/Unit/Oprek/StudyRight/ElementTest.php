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
  private $studyRightDecodedResponses;

  public function setUp() {
    parent::setUp();
    $this->studyRightDecodedResponses = [
      0 => Json::decode(file_get_contents(__DIR__ . '/../study_rights_response.0.json')),
      4 => Json::decode(file_get_contents(__DIR__ . '/../study_rights_response.4.json')),
    ];
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
        2 => 'KH60_001',
        3 => 'SH60_039',
        4 => '03417',
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
        $element = new Element($this->studyRightDecodedResponses[0]['data'][$study_right_index]['elements'][$element_index]);
        $this->assertEquals($expectedCode, $element->getCode());
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
        3 => 30,
        4 => 40,
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
        $element = new Element($this->studyRightDecodedResponses[0]['data'][$study_right_index]['elements'][$element_index]);
        $this->assertEquals($expectedId, $element->getId());
      }
    }
  }

  /**
   * @test
   */
  public function isActiveShouldReturnCorrectBoolean() {
    $expectedBooleans = [
      0 => [
        0 => [
          '2018-10-24T11:00:00.000Z' => [TRUE, TRUE, TRUE, TRUE, FALSE],
          '1990-10-10T10:00:00.000Z' => [FALSE, FALSE, FALSE, FALSE, FALSE],
        ],
        1 => [
          '2018-10-24T11:00:00.000Z' => [FALSE, TRUE, TRUE, FALSE, FALSE],
          '1990-10-10T10:00:00.000Z' => [FALSE, FALSE, FALSE, FALSE, FALSE],
        ],
      ],
      4 => [
        0 => [
          '2018-10-24T11:00:00.000Z' => [TRUE, TRUE, FALSE, TRUE, TRUE, FALSE],
          '2018-08-24T11:00:00.000Z' => [TRUE, TRUE, TRUE, FALSE, FALSE, TRUE],
          '1990-10-10T10:00:00.000Z' => [FALSE, FALSE, FALSE, FALSE, FALSE],
        ],
        1 => [
          '2018-10-24T11:00:00.000Z' => [FALSE, TRUE, TRUE, FALSE, FALSE],
          '2018-08-24T11:00:00.000Z' => [TRUE, TRUE, TRUE, TRUE, TRUE],
          '1990-10-10T10:00:00.000Z' => [FALSE, FALSE, FALSE, FALSE, FALSE],
        ],
      ],
    ];
    foreach ($expectedBooleans as $response_index => $study_rights) {
      foreach ($study_rights as $study_right_index => $study_right) {
        foreach ($study_right as $date => $elements) {
          foreach ($elements as $element_index => $expectedBooleanResponse) {
            $element = new Element($this->studyRightDecodedResponses[$response_index]['data'][$study_right_index]['elements'][$element_index]);
            $element->setDate(new DateTime($date));
            $this->assertEquals($expectedBooleanResponse, $element->isActive());
          }
        }
      }
    }
  }

}
