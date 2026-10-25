<?php

declare(strict_types=1);

namespace Drupal\ancora_academic\Plugin\Validation\Constraint;

use Drupal\ancora_academic\Entity\Cohort;
use Drupal\ancora_academic\Entity\StudentGroup;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Validates the AncoraGroupCohorts constraint.
 */
final class GroupCohortsConstraintValidator extends ConstraintValidator {

  /**
   * {@inheritdoc}
   */
  public function validate(mixed $value, Constraint $constraint): void {
    if (!$constraint instanceof GroupCohortsConstraint) {
      throw new UnexpectedTypeException($constraint, GroupCohortsConstraint::class);
    }
    if (!$value instanceof StudentGroup) {
      return;
    }
    $specialty = (string) $value->get('specialty')->target_id;
    $level = (string) $value->get('level')->target_id;
    foreach ($value->get('cohorts')->referencedEntities() as $cohort) {
      if ($cohort instanceof Cohort
        && ((string) $cohort->get('specialty')->target_id !== $specialty || (string) $cohort->get('level')->target_id !== $level)) {
        $this->context->buildViolation($constraint->message)
          ->setParameter('%label', (string) $cohort->label())
          ->atPath('cohorts')
          ->addViolation();
      }
    }
  }

}
