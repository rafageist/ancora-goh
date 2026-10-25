<?php

declare(strict_types=1);

namespace Drupal\ancora;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;

/**
 * An academic term: a dated period that timetables are planned for.
 *
 * This is the foundation proof entity (#69). The real academic model is mapped
 * in #70.
 */
interface AcademicTermInterface extends ContentEntityInterface, EntityChangedInterface {

  /**
   * Returns the short unique code of the term, for example "2026-S1".
   */
  public function getCode(): string;

  /**
   * Returns the first day of the term, as an ISO 8601 date (Y-m-d).
   */
  public function getStartDate(): ?string;

  /**
   * Returns the last day of the term, as an ISO 8601 date (Y-m-d).
   */
  public function getEndDate(): ?string;

}
