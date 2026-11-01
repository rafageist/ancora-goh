<?php

declare(strict_types=1);

namespace Drupal\ancora_scheduling\Timetable;

/**
 * A refused placement move (#130): nothing was written.
 *
 * The reason is a code; PlacementModification::message() gives the text the
 * user reads, with the placeholder values.
 */
final class PlacementModificationException extends \RuntimeException {

  public function __construct(
    public readonly string $reason,
    public readonly array $arguments = [],
  ) {
    parent::__construct('Placement move refused: ' . $reason);
  }

}
