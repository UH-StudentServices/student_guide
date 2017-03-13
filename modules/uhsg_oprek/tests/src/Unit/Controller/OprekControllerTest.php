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

  /** @var FieldItemListInterface */
  private $fieldItemList;

  /** @var OprekController */
  private $oprekController;

  /** @var OprekServiceInterface */
  private $oprekService;

  /** @var TypedDataInterface */
  private $typedData;

  /** @var UserInterface */
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
