<?php

declare(strict_types=1);

namespace Drupal\ancora_academic\Plugin\Validation\Constraint;

use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Validation\Attribute\Constraint;
use Symfony\Component\Validator\Attribute\HasNamedArguments;
use Symfony\Component\Validator\Constraint as SymfonyConstraint;

/**
 * A record references only records of its creator, and of its own plan.
 *
 * - A reference to a catalogue record (activity type, specialty, level,
 *   cohort, subject, room, teacher) must name one of the same creator. In
 *   the legacy UI every choice list is the creator's: the classification,
 *   specialty and level combos load "creador = $_SESSION['user']", and the
 *   teacher and room candidates of a template are the template creator's
 *   (recurso/dame_profesores_no_asignados_a.sql, dame_lugares_no_asignados_a
 *   .sql; inventory PAR-002, PAR-003). The legacy handlers themselves do not
 *   check it (quirk Q-12, a security behaviour that is not reproduced).
 * - A reference to a record of a plan (period, group, template) must name
 *   one of the same plan, as the legacy screens navigate inside one plan.
 */
#[Constraint(
  id: 'AncoraSameCreator',
  label: new TranslatableMarkup('References to the same creator and plan', [], ['context' => 'Validation']),
  type: 'entity',
)]
final class SameCreatorConstraint extends SymfonyConstraint {

  /**
   * Constructs the constraint.
   *
   * @param mixed $options
   *   Unused; options are named arguments.
   * @param string $creatorMessage
   *   The violation message for a record of another creator.
   * @param string $planMessage
   *   The violation message for a record of another plan.
   * @param array|null $groups
   *   The validation groups.
   * @param mixed $payload
   *   The payload.
   */
  #[HasNamedArguments]
  public function __construct(
    mixed $options = NULL,
    public string $creatorMessage = '%label belongs to another user.',
    public string $planMessage = '%label belongs to another plan.',
    ?array $groups = NULL,
    mixed $payload = NULL,
  ) {
    parent::__construct($options, $groups, $payload);
  }

}
