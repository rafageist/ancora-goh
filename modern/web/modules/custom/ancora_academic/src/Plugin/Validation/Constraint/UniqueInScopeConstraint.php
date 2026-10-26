<?php

declare(strict_types=1);

namespace Drupal\ancora_academic\Plugin\Validation\Constraint;

use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Validation\Attribute\Constraint;
use Symfony\Component\Validator\Attribute\HasNamedArguments;
use Symfony\Component\Validator\Constraint as SymfonyConstraint;

/**
 * A combination of field values is unique within the record's scope.
 *
 * The scope is the plan for the records of a plan (periods, groups,
 * templates, assignments), and the creator for plans and catalogue records:
 * a code is unique among one user's teachers, so two users of a site can
 * both have a teacher "T1", as two legacy users each had their own rows.
 * Codes have no legacy counterpart (legacy ids are generated); they are the
 * Drupal handle of a record. The violation is reported on the last field.
 */
#[Constraint(
  id: 'AncoraUniqueInScope',
  label: new TranslatableMarkup('Unique in the plan or among the creator records', [], ['context' => 'Validation']),
  type: 'entity',
)]
final class UniqueInScopeConstraint extends SymfonyConstraint {

  /**
   * Constructs the constraint.
   *
   * @param mixed $options
   *   Unused; options are named arguments.
   * @param list<string> $fields
   *   The single-valued fields whose combination must be unique.
   * @param string $message
   *   The violation message.
   * @param array|null $groups
   *   The validation groups.
   * @param mixed $payload
   *   The payload.
   */
  #[HasNamedArguments]
  public function __construct(
    mixed $options = NULL,
    public array $fields = ['code'],
    public string $message = 'The code %value is already used by another record of this plan or of the same creator.',
    ?array $groups = NULL,
    mixed $payload = NULL,
  ) {
    parent::__construct($options, $groups, $payload);
  }

}
