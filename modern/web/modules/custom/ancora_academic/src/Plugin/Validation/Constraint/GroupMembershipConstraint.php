<?php

declare(strict_types=1);

namespace Drupal\ancora_academic\Plugin\Validation\Constraint;

use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Validation\Attribute\Constraint;
use Symfony\Component\Validator\Attribute\HasNamedArguments;
use Symfony\Component\Validator\Constraint as SymfonyConstraint;

/**
 * The legacy guard of a cohort move (mueve_o_crea_brigada_de_grupo).
 *
 * A cohort cannot be added to a group that has placed activities, nor moved
 * out of another group of the same slot that has them (GroupMembership).
 */
#[Constraint(
  id: 'AncoraGroupMembership',
  label: new TranslatableMarkup('Cohort membership of a student group', [], ['context' => 'Validation']),
  type: 'entity',
)]
final class GroupMembershipConstraint extends SymfonyConstraint {

  /**
   * Constructs the constraint.
   *
   * @param mixed $options
   *   Unused; options are named arguments.
   * @param string $targetMessage
   *   The message when the group itself has placed activities.
   * @param string $unknownMessage
   *   The message when the placed activities cannot be verified.
   * @param string $sourceMessage
   *   The message when the group the cohort would leave has them.
   * @param array|null $groups
   *   The validation groups.
   * @param mixed $payload
   *   The payload.
   */
  #[HasNamedArguments]
  public function __construct(
    mixed $options = NULL,
    public string $targetMessage = 'The cohort %cohort cannot join the group %group: the group already has placed activities. Delete its placements first (the legacy application refuses this move).',
    public string $unknownMessage = 'The cohort %cohort cannot be added now: the placed activities of the group %group cannot be verified right now. Try again later.',
    public string $sourceMessage = 'The cohort %cohort cannot move to this group: it would leave the group %source, which already has placed activities. Delete its placements first (the legacy application refuses this move).',
    ?array $groups = NULL,
    mixed $payload = NULL,
  ) {
    parent::__construct($options, $groups, $payload);
  }

}
