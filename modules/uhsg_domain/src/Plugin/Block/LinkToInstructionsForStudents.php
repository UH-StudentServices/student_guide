<?php

namespace Drupal\uhsg_domain\Plugin\Block;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Link;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Url;

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
    return !\Drupal::service('uhsg_domain.domain')->isStudentDomain()
      ? AccessResult::allowed()
      : AccessResult::forbidden();
  }

  public function build() {
    return [
      'content' => [
        '#markup' => $this->getMarkup()
      ],
    ];
  }

  private function getMarkup() {
    $url = $this->getUrl();
    $markup = '<div class="item-list"><ul class="list-of-links">';
    $markup .= $this->getLinkItemMarkup($url, \Drupal::service('uhsg_domain.domain')->getStudentDomainLabel());
    $markup .= $this->getLinkItemMarkup("$url/news", $this->t('Notifications for students'));
    $markup .= '</ul></div>';

    return $markup;
  }

  private function getUrl() {
    $url = \Drupal::service('uhsg_domain.domain')->getStudentDomainUrl();
    $language = \Drupal::languageManager()->getCurrentLanguage()->getId();

    return $url . $language;
  }

  private function getLinkItemMarkup($uri, $label) {
    $url = Url::fromUri($uri, [
      'attributes' => [
        'class' => [
          'list-of-links__link',
          'button--action',
          'icon--external-link',
        ],
      ],
    ]);

    $link = Link::fromTextAndUrl($label, $url)->toString();

    return "<li>$link</li>";
  }
}
