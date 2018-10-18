<?php

use Drupal\Tests\UnitTestCase;
use Drupal\uhsg_oprek\Oprek\StudyRight\Element;

/**
 * @group uhsg
 */
class ElementTest extends UnitTestCase {

  const CODE_KEY = 'code';
  const CODE_VALUE = 'code value';

  /** @var \Drupal\uhsg_oprek\Oprek\StudyRight\Element*/
  private $element;

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

}
