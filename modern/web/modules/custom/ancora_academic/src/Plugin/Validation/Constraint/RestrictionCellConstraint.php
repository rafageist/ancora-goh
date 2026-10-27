<?php

declare(strict_types=1);

namespace Drupal\ancora_academic\Plugin\Validation\Constraint;

use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Validation\Attribute\Constraint;
use Symfony\Component\Validator\Attribute\HasNamedArguments;
use Symfony\Component\Validator\Constraint as SymfonyConstraint;

/**
 * The legacy keys of a nominal restriction row.
 *
 * UNIQUE (object, period, day, slot) of the restriccion_de_* tables: one row
 * per object and cell, and no manual row on a cell that a placement of the
 * brigade, teacher or room occupies (the occupancy rows share the key).
 */
#[Constraint(
  id: 'AncoraRestrictionCell',
  label: new TranslatableMarkup('Time restriction cell', [], ['context' => 'Validation']),
  type: 'entity',
)]
final class RestrictionCellConstraint extends SymfonyConstraint {

  /**
   * Constructs the constraint.
   *
   * @param mixed $options
   *   Unused; options are named arguments.
   * @param string $targetMessage
   *   The object of the kind is missing, or another one is set.
   * @param string $duplicateMessage
   *   The cell is already restricted for the object.
   * @param string $occupiedMessage
   *   A placement of the object occupies the cell.
   * @param string $unknownMessage
   *   The placements cannot be read right now.
   * @param array|null $groups
   *   The validation groups.
   * @param mixed $payload
   *   The payload.
   */
  #[HasNamedArguments]
  public function __construct(
    mixed $options = NULL,
    public string $targetMessage = 'A time restriction needs exactly the object of its kind.',
    public string $duplicateMessage = 'Day %day, slot %slot is already restricted for %object.',
    public string $occupiedMessage = 'Day %day, slot %slot cannot be restricted for %object: a placed activity occupies it. Delete that placement first (the legacy tables refuse a restriction on an occupied cell).',
    public string $unknownMessage = 'Day %day, slot %slot cannot be restricted for %object now: its placed activities cannot be verified right now. Try again later.',
    ?array $groups = NULL,
    mixed $payload = NULL,
  ) {
    parent::__construct($options, $groups, $payload);
  }

}
