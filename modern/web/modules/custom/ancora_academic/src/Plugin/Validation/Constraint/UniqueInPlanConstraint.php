<?php

declare(strict_types=1);

namespace Drupal\ancora_academic\Plugin\Validation\Constraint;

use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Validation\Attribute\Constraint;
use Symfony\Component\Validator\Attribute\HasNamedArguments;
use Symfony\Component\Validator\Constraint as SymfonyConstraint;

/**
 * A combination of field values is unique within the plan of the record.
 *
 * For example the code of a teacher, or the (subject, period, sequence) of an
 * activity template. The violation is reported on the last field.
 */
#[Constraint(
  id: 'AncoraUniqueInPlan',
  label: new TranslatableMarkup('Unique in the plan', [], ['context' => 'Validation']),
  type: 'entity',
)]
final class UniqueInPlanConstraint extends SymfonyConstraint {

  /**
   * Constructs the constraint.
   *
   * @param mixed $options
   *   Unused; options are named arguments.
   * @param list<string> $fields
   *   The single-valued fields whose combination must be unique in the plan.
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
    public string $message = 'The code %value is already used by another record of this plan.',
    ?array $groups = NULL,
    mixed $payload = NULL,
  ) {
    parent::__construct($options, $groups, $payload);
  }

}
