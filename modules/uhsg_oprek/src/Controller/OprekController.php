<?php

namespace Drupal\uhsg_oprek\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\uhsg_oprek\Oprek\OprekServiceInterface;
use Drupal\user\UserInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class OprekController extends ControllerBase {

  /**
   * @var OprekServiceInterface
   */
  protected $service;

  public function __construct(OprekServiceInterface $service) {
    $this->service = $service;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('uhsg_oprek.oprek_service')
    );
  }

  public function infoPage(UserInterface $user) {

    try {
      $version = $this->service->getVersion();
    }
    catch (\Exception $e) {
      $version = 'Error: ' . $e->getMessage();
    }

    $markup = 'Service version: ' . $version;

    if (!$user->get('field_student_number')->isEmpty()) {
      // We have student number available
      $student_id = $user->get('field_student_number')->first()->getString();
      try {
        $study_rights = $this->service->getStudyRights($student_id);
        $markup .= '<br/><pre>' . print_r($study_rights,1) . '</pre>';
      }
      catch (\Exception $e) {
        $markup .= '<br/><pre>Error: ' . $e->getMessage() . '</pre>';
      }
    }
    return [
      '#markup' => $markup,
      '#cache' => [
        'tags' => ['user:' . $user->id()],
      ],
    ];
  }

}
