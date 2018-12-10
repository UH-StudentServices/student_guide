<?php

namespace Drupal\uhsg_domain;

use Drupal\domain\DomainNegotiatorInterface;

class DomainService {

  const STUDENT_DOMAIN_ID = 'guide_student_helsinki_fi';
  const TEACHER_DOMAIN_ID = 'guide_teacher_helsinki_fi';

  /** @var DomainNegotiatorInterface */
  private $domainNegotiator;

  public function __construct(DomainNegotiatorInterface $domainNegotiator) {
    $this->domainNegotiator = $domainNegotiator;
  }

  public function getActiveDomainId() {
    return $this->domainNegotiator->getActiveId();
  }

  public function isStudentDomain() {
    return $this->getActiveDomainId() == self::STUDENT_DOMAIN_ID;
  }

  public function isTeacherDomain() {
    return $this->getActiveDomainId() == self::TEACHER_DOMAIN_ID;
  }
}
