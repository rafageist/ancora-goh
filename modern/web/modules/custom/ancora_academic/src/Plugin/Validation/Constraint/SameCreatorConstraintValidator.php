<?php

declare(strict_types=1);

namespace Drupal\ancora_academic\Plugin\Validation\Constraint;

use Drupal\ancora_academic\AcademicEntityInterface;
use Drupal\ancora_academic\AcademicEntityTypes;
use Drupal\Core\Field\EntityReferenceFieldItemListInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Validates the AncoraSameCreator constraint.
 */
final class SameCreatorConstraintValidator extends ConstraintValidator {

  /**
   * {@inheritdoc}
   */
  public function validate(mixed $value, Constraint $constraint): void {
    if (!$constraint instanceof SameCreatorConstraint) {
      throw new UnexpectedTypeException($constraint, SameCreatorConstraint::class);
    }
    if (!$value instanceof AcademicEntityInterface) {
      return;
    }
    $creator = $value->getCreatorId();
    $plan = $value->hasField('plan') ? $value->getPlanId() : NULL;
    foreach ($value->getFields(FALSE) as $name => $items) {
      if ($name === 'plan' || $name === 'uid' || !$items instanceof EntityReferenceFieldItemListInterface) {
        continue;
      }
      foreach ($items->referencedEntities() as $referenced) {
        if (!$referenced instanceof AcademicEntityInterface) {
          continue;
        }
        $owned = AcademicEntityTypes::isOwned($referenced->getEntityTypeId());
        if ($owned && $creator !== NULL && $referenced->getCreatorId() !== $creator) {
          $message = $constraint->creatorMessage;
        }
        elseif (!$owned && $plan !== NULL && $referenced->getPlanId() !== $plan) {
          $message = $constraint->planMessage;
        }
        else {
          continue;
        }
        $this->context->buildViolation($message)
          ->setParameter('%label', (string) $referenced->label())
          ->atPath($name)
          ->addViolation();
        break;
      }
    }
  }

}
