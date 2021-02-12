<?php

use Drupal\Tests\UnitTestCase;
use Drupal\uhsg_samlauth\AttributeParser;

/**
 * @group uhsg
 */
class AttributeParserTest extends UnitTestCase {

  const COMMON_NAME = 'common name';
  const EMAIL_ADDRESS = 'email address';
  const GROUP = 'group';
  const LOGOUT_URL = 'logout url';
  const HYPERSONID = 'hy personid';
  const STUDENT_ID = 'student id';
  const STUDENT_ID_PREFIX = 'urn:schac:personalUniqueCode:int:studentID:helsinki.fi:';
  const USER_ID = 'user id';

  /** @var \Drupal\uhsg_samlauth\AttributeParser*/
  private $attributeParser;

  /** @var array*/
  private $attributes = [
    'urn:oid:2.5.4.3' => [self::COMMON_NAME],
    'urn:oid:1.3.6.1.4.1.18869.1.1.1.48' => [self::HYPERSONID],
    'urn:oid:1.3.6.1.4.1.25178.1.2.14' => [self::STUDENT_ID_PREFIX . self::STUDENT_ID],
    'urn:oid:0.9.2342.19200300.100.1.1' => [self::USER_ID],
    'urn:oid:0.9.2342.19200300.100.1.3' => [self::EMAIL_ADDRESS],
    'urn:mace:funet.fi:haka:logout-url' => [self::LOGOUT_URL],
    'urn:mace:funet.fi:helsinki.fi:hyGroupCn' => [self::GROUP],
  ];

  public function setUp() {
    parent::setUp();

    $this->attributeParser = new AttributeParser($this->attributes);
  }

  /**
   * @test
   */
  public function getCommonNameShouldReturnCommonName() {
    $this->assertEquals(self::COMMON_NAME, $this->attributeParser->getCommonName());
  }

  /**
   * @test
   */
  public function getEmailAddressShouldReturnEmailAddress() {
    $this->assertEquals(self::EMAIL_ADDRESS, $this->attributeParser->getEmailAddress());
  }

  /**
   * @test
   */
  public function getGroupsShouldReturnGroups() {
    $this->assertEquals([self::GROUP], $this->attributeParser->getGroups());
  }

  /**
   * @test
   */
  public function getLogoutUrlShouldReturnLogoutUrl() {
    $this->assertEquals(self::LOGOUT_URL, $this->attributeParser->getLogoutUrl());
  }

  /**
   * @test
   */
  public function getOodiUidShouldReturnOodiUid() {
    $this->assertEquals(self::OODI_UID, $this->attributeParser->getOodiUid());
  }

  /**
   * @test
   */
  public function getHyPersonIdShouldReturnHyPersonId() {
    $this->assertEquals(self::HYPERSONID, $this->attributeParser->getHyPersonId());
  }

  /**
   * @test
   */
  public function getStudentIdShouldReturnStudentId() {
    $this->assertEquals(self::STUDENT_ID, $this->attributeParser->getStudentId());
  }

  /**
   * @test
   */
  public function getUserIdShouldReturnUserId() {
    $this->assertEquals(self::USER_ID, $this->attributeParser->getUserId());
  }

}
