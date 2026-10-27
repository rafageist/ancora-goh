<?php

declare(strict_types=1);

namespace Drupal\ancora_academic\Plugin\Validation\Constraint;

use Drupal\ancora_academic\Entity\StudentGroup;
use Drupal\ancora_academic\GroupMembership;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Validates the AncoraGroupMembership constraint.
 *
 * Only the cohorts that the save adds are checked, in the order of
 * mueve_o_crea_brigada_de_grupo(): first the target group, then the group
 * of the same slot that the cohort would leave. Removing a cohort is never
 * refused (custom_EliminaBrigadaDeGrupo.php has no guard).
 */
final class GroupMembershipConstraintValidator extends ConstraintValidator implements ContainerInjectionInterface {

  public function __construct(
    private readonly GroupMembership $membership,
    private readonly EntityTypeManagerInterface $entityTypeManager,
  ) {}

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): static {
    return new static($container->get(GroupMembership::class), $container->get('entity_type.manager'));
  }

  /**
   * {@inheritdoc}
   */
  public function validate(mixed $value, Constraint $constraint): void {
    if (!$constraint instanceof GroupMembershipConstraint) {
      throw new UnexpectedTypeException($constraint, GroupMembershipConstraint::class);
    }
    if (!$value instanceof StudentGroup) {
      return;
    }
    $added = $this->membership->added($value);
    if ($added === []) {
      return;
    }
    $cohorts = $this->entityTypeManager->getStorage('ancora_cohort')->loadMultiple($added);
    $label = static fn (int $id): string => isset($cohorts[$id]) ? (string) $cohorts[$id]->label() : (string) $id;
    $target = $this->membership->hasActivities($value);
    if ($target !== FALSE) {
      foreach ($added as $id) {
        $this->context->buildViolation($target === NULL ? $constraint->unknownMessage : $constraint->targetMessage)
          ->setParameter('%cohort', $label($id))
          ->setParameter('%group', (string) $value->label())
          ->atPath('cohorts')
          ->addViolation();
      }
      return;
    }
    foreach ($added as $id) {
      foreach ($this->membership->sources($value, $id) as $source) {
        assert($source instanceof StudentGroup);
        $placed = $this->membership->hasActivities($source);
        if ($placed === NULL) {
          $this->context->buildViolation($constraint->unknownMessage)
            ->setParameter('%cohort', $label($id))
            ->setParameter('%group', (string) $source->label())
            ->atPath('cohorts')
            ->addViolation();
        }
        elseif ($placed) {
          $this->context->buildViolation($constraint->sourceMessage)
            ->setParameter('%cohort', $label($id))
            ->setParameter('%source', (string) $source->label())
            ->atPath('cohorts')
            ->addViolation();
        }
      }
    }
  }

}
