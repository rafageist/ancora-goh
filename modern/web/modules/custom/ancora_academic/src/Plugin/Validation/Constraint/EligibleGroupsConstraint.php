<?php

declare(strict_types=1);

namespace Drupal\ancora_academic\Plugin\Validation\Constraint;

use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Validation\Attribute\Constraint;
use Symfony\Component\Validator\Attribute\HasNamedArguments;
use Symfony\Component\Validator\Constraint as SymfonyConstraint;

/**
 * The groups of a teacher assignment attend its activity template.
 *
 * A group attends a template when it has the activity type of the template
 * and the specialty and level of its subject (legacy
 * dame_grupos_de_plantilla).
 */
#[Constraint(
  id: 'AncoraEligibleGroups',
  label: new TranslatableMarkup('Groups of a teacher assignment', [], ['context' => 'Validation']),
  type: 'entity',
)]
final class EligibleGroupsConstraint extends SymfonyConstraint {

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
    public string $message = 'The group %label does not attend this activity template: it must have the activity type of the template and the specialty and level of its subject.',
    ?array $groups = NULL,
    mixed $payload = NULL,
  ) {
    parent::__construct($options, $groups, $payload);
  }

}
