<?php

namespace Drupal\uhsg_domain;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\domain\DomainNegotiatorInterface;
use Drupal\domain\DomainStorage;
use Drupal\domain\Entity\Domain;

class DomainService {

  const STUDENT_DOMAIN_ID = 'guide_student_helsinki_fi';
  const TEACHING_DOMAIN_ID = 'guide_teacher_helsinki_fi';

  /** @var DomainNegotiatorInterface */
  private $domainNegotiator;

  /** @var EntityTypeManagerInterface */
  private $entityTypeManager;

  public function __construct(DomainNegotiatorInterface $domainNegotiator, EntityTypeManagerInterface $entityTypeManager) {
    $this->domainNegotiator = $domainNegotiator;
    $this->entityTypeManager = $entityTypeManager;
  }

  public function getActiveDomainId() {
    return $this->domainNegotiator->getActiveId();
  }

  public function isStudentDomain() {
    return $this->getActiveDomainId() == self::STUDENT_DOMAIN_ID;
  }

  public function isTeachingDomain() {
    return $this->getActiveDomainId() == self::TEACHING_DOMAIN_ID;
  }

  public function getStudentDomainUrl() {
    return $this->getDomainUrl(self::STUDENT_DOMAIN_ID);
  }

  public function getStudentDomainLabel() {
    return $this->loadDomain(self::STUDENT_DOMAIN_ID)->label();
  }

  public function getTeachingDomainUrl() {
    return $this->getDomainUrl(self::TEACHING_DOMAIN_ID);
  }

  public function getTeachingDomainLabel() {
    return $this->loadDomain(self::TEACHING_DOMAIN_ID)->label();
  }

  private function getDomainUrl($domainId) {
    return $this->loadDomain($domainId)->getPath();
  }

  /**
   * @param $domainId
   * @return Domain
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  private function loadDomain($domainId) {
    /** @var $domainStorage DomainStorage */
    $domainStorage = $this->entityTypeManager->getStorage('domain');

    return $domainStorage->load($domainId);
  }
}
