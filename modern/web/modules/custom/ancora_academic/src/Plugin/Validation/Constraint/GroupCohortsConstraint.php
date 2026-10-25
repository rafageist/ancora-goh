<?php

declare(strict_types=1);

namespace Drupal\ancora_academic\Plugin\Validation\Constraint;

use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Validation\Attribute\Constraint;
use Symfony\Component\Validator\Attribute\HasNamedArguments;
use Symfony\Component\Validator\Constraint as SymfonyConstraint;

/**
 * The cohorts of a student group have the specialty and level of the group.
 */
#[Constraint(
  id: 'AncoraGroupCohorts',
  label: new TranslatableMarkup('Cohorts of a student group', [], ['context' => 'Validation']),
  type: 'entity',
)]
final class GroupCohortsConstraint extends SymfonyConstraint {

  /**
   * Constructs the constraint.
   *
   * @param mixed $options
   *   Unused; options are named arguments.
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
    public string $message = 'The cohort %label does not have the specialty and level of the group.',
    ?array $groups = NULL,
    mixed $payload = NULL,
  ) {
    parent::__construct($options, $groups, $payload);
  }

}
