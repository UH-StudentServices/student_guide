<?php

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\TypedData\TypedDataInterface;
use Drupal\Tests\UnitTestCase;
use Drupal\uhsg_oprek\Controller\OprekController;
use Drupal\uhsg_oprek\Oprek\OprekServiceInterface;
use Drupal\user\UserInterface;

/**
 * @group uhsg
 */
class OprekControllerTest extends UnitTestCase {

  const FIELD_STUDENT_NUMBER = 'field_student_number';
  const STUDENT_NUMBER = 111;

  /** @var \Drupal\Core\Field\FieldItemListInterface*/
  private $fieldItemList;

  /** @var \Drupal\uhsg_oprek\Controller\OprekController*/
  private $oprekController;

  /** @var \Drupal\uhsg_oprek\Oprek\OprekServiceInterface*/
  private $oprekService;

  /** @var \Drupal\Core\TypedData\TypedDataInterface*/
  private $typedData;

  /** @var \Drupal\user\UserInterface*/
  private $user;

  public function setUp() {
    parent::setUp();

    $this->oprekService = $this->prophesize(OprekServiceInterface::class);

    $this->typedData = $this->prophesize(TypedDataInterface::class);
    $this->typedData->getString()->willReturn(self::STUDENT_NUMBER);

    $this->fieldItemList = $this->prophesize(FieldItemListInterface::class);
    $this->fieldItemList->first()->willReturn($this->typedData);
    $this->fieldItemList->isEmpty()->willReturn(FALSE);

    $this->user = $this->prophesize(UserInterface::class);
    $this->user->get(self::FIELD_STUDENT_NUMBER)->willReturn($this->fieldItemList);
    $this->user->id()->willReturn();

    $this->oprekController = new OprekController($this->oprekService->reveal());
  }

  /**
   * @test
   */
  public function shouldCallOprekServiceWhenTheUserHasStudentNumber() {
    $this->user->hasField(self::FIELD_STUDENT_NUMBER)->willReturn(TRUE);
    $this->oprekService->getStudyRights(self::STUDENT_NUMBER)->shouldBeCalled();

    $this->oprekController->infoPage($this->user->reveal());
  }

}
