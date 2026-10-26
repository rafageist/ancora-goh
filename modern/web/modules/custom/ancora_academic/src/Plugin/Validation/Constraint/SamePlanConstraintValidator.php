<?php

declare(strict_types=1);

namespace Drupal\ancora_academic\Plugin\Validation\Constraint;

use Drupal\ancora_academic\AcademicEntityInterface;
use Drupal\Core\Field\EntityReferenceFieldItemListInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Validates the AncoraSamePlan constraint.
 */
final class SamePlanConstraintValidator extends ConstraintValidator {

  /**
   * {@inheritdoc}
   */
  public function validate(mixed $value, Constraint $constraint): void {
    if (!$constraint instanceof SamePlanConstraint) {
      throw new UnexpectedTypeException($constraint, SamePlanConstraint::class);
    }
    if (!$value instanceof AcademicEntityInterface) {
      return;
    }
    $plan_id = $value->getPlanId();
    if ($plan_id === NULL) {
      return;
    }
    foreach ($value->getFields(FALSE) as $name => $items) {
      if ($name === 'plan' || !$items instanceof EntityReferenceFieldItemListInterface) {
        continue;
      }
      foreach ($items->referencedEntities() as $referenced) {
        if ($referenced instanceof AcademicEntityInterface && $referenced->getPlanId() !== $plan_id) {
          $this->context->buildViolation($constraint->message)
            ->setParameter('%label', (string) $referenced->label())
            ->atPath($name)
            ->addViolation();
          break;
        }
      }
    }
  }

}
