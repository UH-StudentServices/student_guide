<?php

namespace Drupal\uhsg_obar;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Path\PathMatcherInterface;
use Drupal\Core\Routing\UrlGeneratorInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Url;
use Drupal\user\Entity\User;
use Firebase\JWT\JWT as Firebase_JWT;

class Jwt {

  /**
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected $config;

  /**
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $user;

  /**
   * @var \Drupal\Core\Routing\UrlGeneratorInterface
   */
  protected $urlGenerator;

  /**
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * The path matcher.
   *
   * @var \Drupal\Core\Path\PathMatcherInterface
   */
  protected $pathMatcher;

  /**
   * Jwt constructor.
   * @param \Drupal\Core\Config\ConfigFactory $configFactory
   * @param \Drupal\Core\Session\AccountInterface $user
   * @param \Drupal\Core\Routing\UrlGeneratorInterface $urlGenerator
   * @param \Drupal\Core\Language\LanguageManagerInterface $languageManager
   * @param \Drupal\Core\Path\PathMatcherInterface $path_matcher
   */
  public function __construct(ConfigFactory $config, AccountInterface $user, UrlGeneratorInterface $urlGenerator, LanguageManagerInterface $languageManager, PathMatcherInterface $path_matcher) {
    $this->config = $config->get('uhsg_obar.settings');
    $this->user = $user;
    $this->urlGenerator = $urlGenerator;
    $this->languageManager = $languageManager;
    $this->pathMatcher = $path_matcher;
  }

  public function generateToken() {
    return Firebase_JWT::encode($this->getPayload(), $this->getKeyContents(), 'RS256');
  }

  private function getKeyContents() {
    $private_key_filepath = $this->config->get('private_key_path');
    if (!empty($private_key_filepath)) {
      return file_get_contents($private_key_filepath);
    }
    throw new \Exception('Private key path configuration is missing.');
  }

  private function getPayload() {
    return (object) [
      'loginEndpoint' => $this->urlGenerator->generateFromRoute('samlauth.saml_controller_login'),
      'logoutEndpoint' => $this->urlGenerator->generateFromRoute('samlauth.saml_controller_logout'),
      'user' => $this->getUser(),
      'currentLang' => $this->languageManager->getCurrentLanguage()->getId(),
      'languageSelectEndpoints' => $this->getLanguageSelectEndpoints(),
      'footer' => $this->getFooter()
    ];
  }

  private function getUser() {
    if ($this->user->isAuthenticated()) {
      $user = User::load($this->user->id());
      $oodiId = $user->get('field_oodi_uid')->value;
      $userName = $user->hasField('field_common_name') ? $user->get('field_common_name')->value : null;
      $firstName = $this->getFirstName($userName);
      $lastName = $this->getLastName($userName);

      $formattedUser = [
        'oodiId' => $oodiId ? $oodiId : '',
        'userName' => !empty($userName) ? $userName : $user->getDisplayName(),
      ];

      if (!empty($firstName) && !empty($lastName)) {
        $formattedUser['firstName'] = $firstName;
        $formattedUser['lastName'] = $lastName;
      }

      return (object) $formattedUser;
    }

    return NULL;
  }

  private function getFirstName($name) {
    return is_string($name) && !empty($name) ? explode(' ', $name)[0] : '';
  }

  private function getLastName($name) {
    if (is_string($name) && !empty($name)) {
      $parts = explode(' ', $name);

      return count($parts) > 1 ? end($parts) : '';
    }

    return '';
  }

  private function getLanguageSelectEndpoints() {
    $endpoints = [];
    $route_name = $this->pathMatcher->isFrontPage() ? '<front>' : '<current>';
    $links = $this->languageManager->getLanguageSwitchLinks(LanguageInterface::TYPE_INTERFACE, Url::fromRoute($route_name));

    if(isset($links->links)) {
      foreach ($links->links as $langcode => $link) {
        $options = $link['url']->getOptions();
        $options['language'] = $link['language'];
        $link['url']->setOptions($options);
        $endpoints[$langcode] = $link['url']->toString();
      }
      return (object) $endpoints;
    }
  }

  /**
   * The simplest hard-coded implementation for application-specific footer.
   * Not configurable for now. Links common for all services will be refactored
   * away in the future, leaving only the truly application-specific
   * configuration.
   */
  private function getFooter() {
    $footer = [
      'serviceName' => [
        'Opiskelu',
        'Studier',
        'Studies'
      ],
      'applicationName' => [
        'Opiskelijan ohjeet',
        'Instruktioner för studerande',
        'Instructions for students'
      ],
      'items' => [
        'bulletin' => [
          'name' => [
            'Tiedotearkisto',
            'Meddelanden arkiv',
            'Bulletin archive'
          ],
          'url' => [
            'https://studies.helsinki.fi/ohjeet/news',
            'https://studies.helsinki.fi/instruktioner/news',
            'https://studies.helsinki.fi/instructions/news'
          ]
        ],
        'studentServices' => [
          'name' => [
            'Opiskelijaneuvonta',
            'Studentservicen',
            'Student Services'
          ],
          'url' => [
            'https://studies.helsinki.fi/ohjeet/artikkeli/opiskelijaneuvonta',
            'https://studies.helsinki.fi/instruktioner/artikel/studentservicen',
            'https://studies.helsinki.fi/instructions/article/student-services'
          ]
        ],
        'studentServicesAppointment' => [
          'name' => [
            'Opiskelijaneuvonnan ajanvaraus',
            'Tidsbokning till Studentservicen',
            'Appointments to Student Services'
          ],
          'url' => [
            'https://secure.vihta.com/public-ng/studenthelsinki/#/home',
            'https://secure.vihta.com/public-ng/studenthelsinki/#/home',
            'https://secure.vihta.com/public-ng/studenthelsinki/#/home'
          ]
        ],
        'careerServices' => [
          'name' => [
            'Urapalvelut',
            'Karriärservicen',
            'Career Services'
          ],
          'url' => [
            'https://studies.helsinki.fi/ohjeet/artikkeli/urapalvelujen-ohjaus-some-ja-yhteystiedot',
            'https://studies.helsinki.fi/instruktioner/artikel/karriarservicens-vagledning-evenemang-och-kontaktuppgifter',
            'https://studies.helsinki.fi/instructions/article/career-servicesguidance-social-media-and-contact-details'
          ]
        ],
        'exchangeServices' => [
          'name' => [
            'Liikkuvuuspalvelut',
            'Mobilitetsservicen',
            'International Exchange Services'
          ],
          'url' => [
            'https://studies.helsinki.fi/ohjeet/artikkeli/ota-yhteytta-liikkuvuuspalveluihin',
            'https://studies.helsinki.fi/instruktioner/artikel/kontakta-mobilitetsservicen',
            'https://studies.helsinki.fi/instructions/article/contact-international-exchange-services'
          ]
        ],
        'dataProtectionStatement' => [
          'name' => [
            'Tietosuojailmoitus',
            'Dataskyddsmeddelande',
            'Data Protection Statement'
          ],
          'url' => [
            'https://studies.helsinki.fi/ohjeet/artikkeli/tietosuojailmoitus',
            'https://studies.helsinki.fi/instruktioner/artikel/dataskyddsmeddelande',
            'https://studies.helsinki.fi/instructions/article/data-protection-statement'
          ]
        ]
      ]
    ];

    return (object) $footer;
  }
}
