<?php

namespace Drupal\uhsg_domain\Plugin\Block;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Provides a 'link_to_instructions_for_students' block.
 *
 * @Block(
 *  id = "link_to_instructions_for_students",
 *  admin_label = @Translation("Link to Instructions for students"),
 * )
 */
class LinkToInstructionsForStudents extends BlockBase {

  use StringTranslationTrait;

  /**
   * @inheritdoc
   */
  public function access(AccountInterface $account, $return_as_object = FALSE) {
    return \Drupal::service('uhsg_domain.domain')->isTeacherDomain()
      ? AccessResult::allowed()
      : AccessResult::forbidden();
  }

  public function build() {
    $url = \Drupal::service('uhsg_domain.domain')->getStudentDomainUrl();
    $label = \Drupal::service('uhsg_domain.domain')->getStudentDomainLabel();
    $markup  = '<div class="item-list"><ul class="list-of-links"><li>';
    $markup .= '<a href="' . $url . '" class="list-of-links__link button--action icon--arrow-offsite">';
    $markup .= $label;
    $markup .= '</a></li></ul></div>';

    return [
      'content' => [
        '#markup' => $markup
      ],
    ];
  }

}
