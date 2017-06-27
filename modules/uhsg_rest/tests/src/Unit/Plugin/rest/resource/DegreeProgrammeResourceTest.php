<?php

use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\taxonomy\TermInterface;
use Drupal\taxonomy\TermStorageInterface;
use Drupal\Tests\UnitTestCase;
use Drupal\uhsg_rest\Plugin\rest\resource\DegreeProgrammeResource;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;

/**
 * @group uhsg
 */
class DegreeProgrammeResourceTest extends UnitTestCase {

  const DEGREE_PROGRAMME_CODE = 'code';
  const DEGREE_PROGRAMME_LABEL = 'label';
  const LANGUAGE_EN = 'en';
  const LANGUAGE_FI = 'fi';
  const LANGUAGE_SV = 'sv';

  /** @var DegreeProgrammeResource */
  private $degreeProgrammeResource;

  /** @var EntityRepositoryInterface */
  private $entityRepository;

  /** @var EntityTypeManagerInterface */
  private $entityTypeManager;

  /** @var FieldItemListInterface */
  private $fieldItemList;

  /** @var LanguageInterface */
  private $languageEN;

  /** @var LanguageInterface */
  private $languageFI;

  /** @var LanguageInterface */
  private $languageSV;

  /** @var LanguageManagerInterface */
  private $languageManager;

  /** @var LoggerInterface */
  private $logger;

  /** @var TermInterface */
  private $term;

  /** @var TermStorageInterface */
  private $termStorage;

  public function setUp() {
    parent::setUp();

    $codeValue = new stdClass();
    $codeValue->value = self::DEGREE_PROGRAMME_CODE;

    $this->term = $this->prophesize(TermInterface::class);
    $this->term->get('field_code')->willReturn($codeValue);
    $this->term->getCacheContexts()->willReturn([]);
    $this->term->getCacheMaxAge()->willReturn(0);
    $this->term->getCacheTags()->willReturn([]);
    $this->term->label()->willReturn(self::DEGREE_PROGRAMME_LABEL);

    $this->entityRepository = $this->prophesize(EntityRepositoryInterface::class);
    $this->entityRepository->getTranslationFromContext(Argument::any(), Argument::any())->willReturn($this->term);

    $this->termStorage = $this->prophesize(TermStorageInterface::class);
    $this->termStorage->loadTree('degree_programme', 0, NULL, TRUE)->willReturn([$this->term]);

    $this->entityTypeManager = $this->prophesize(EntityTypeManagerInterface::class);
    $this->entityTypeManager->getStorage('taxonomy_term')->willReturn($this->termStorage);

    $this->languageEN = $this->prophesize(LanguageInterface::class);
    $this->languageEN->getId()->willReturn(self::LANGUAGE_EN);
    $this->languageFI = $this->prophesize(LanguageInterface::class);
    $this->languageFI->getId()->willReturn(self::LANGUAGE_FI);
    $this->languageSV = $this->prophesize(LanguageInterface::class);
    $this->languageSV->getId()->willReturn(self::LANGUAGE_SV);

    $this->languageManager = $this->prophesize(LanguageManagerInterface::class);
    $this->languageManager->getLanguages()->willReturn([$this->languageEN, $this->languageFI, $this->languageSV]);

    $this->logger = $this->prophesize(LoggerInterface::class);

    $this->degreeProgrammeResource = new DegreeProgrammeResource(
      [],
      NULL,
      NULL,
      [],
      $this->logger->reveal(),
      $this->entityRepository->reveal(),
      $this->entityTypeManager->reveal(),
      $this->languageManager->reveal()
    );
  }

  /**
   * @test
   */
  public function getShouldReturnDegreeProgrammesIncludingCodeAndNameTranslations() {
    $degreeProgrammeResponseData = $this->degreeProgrammeResource->get()->getResponseData();

    $expectedResponseData[] = [
      'code' => self::DEGREE_PROGRAMME_CODE,
      'name' => [
        'en' => self::DEGREE_PROGRAMME_LABEL,
        'fi' => self::DEGREE_PROGRAMME_LABEL,
        'sv' => self::DEGREE_PROGRAMME_LABEL
      ]
    ];

    $this->assertEquals($expectedResponseData, $degreeProgrammeResponseData);
  }
}
