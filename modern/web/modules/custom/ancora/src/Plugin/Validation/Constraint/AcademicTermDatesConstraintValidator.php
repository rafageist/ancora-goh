<?php

declare(strict_types=1);

namespace Drupal\ancora\Plugin\Validation\Constraint;

use Drupal\ancora\AcademicTermInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Validates the AncoraAcademicTermDates constraint.
 */
final class AcademicTermDatesConstraintValidator extends ConstraintValidator {

  /**
   * {@inheritdoc}
   */
  public function validate(mixed $value, Constraint $constraint): void {
    if (!$constraint instanceof AcademicTermDatesConstraint) {
      throw new UnexpectedTypeException($constraint, AcademicTermDatesConstraint::class);
    }
    if (!$value instanceof AcademicTermInterface) {
      return;
    }
    $start = $value->getStartDate();
    $end = $value->getEndDate();
    // Missing dates are reported by the required-field constraints. Dates are
    // stored as Y-m-d, so string order is chronological order.
    if ($start !== NULL && $end !== NULL && strcmp($end, $start) < 0) {
      $this->context->buildViolation($constraint->message)
        ->setParameter('%start', $start)
        ->setParameter('%end', $end)
        ->atPath('ends_on')
        ->addViolation();
    }
  }

}
