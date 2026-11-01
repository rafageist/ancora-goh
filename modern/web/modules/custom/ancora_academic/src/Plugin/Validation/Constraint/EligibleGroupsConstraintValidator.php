<?php

declare(strict_types=1);

namespace Drupal\ancora_academic\Plugin\Validation\Constraint;

use Drupal\ancora_academic\Entity\StudentGroup;
use Drupal\ancora_academic\Entity\TeacherAssignment;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Validates the AncoraEligibleGroups constraint.
 */
final class EligibleGroupsConstraintValidator extends ConstraintValidator {

  /**
   * {@inheritdoc}
   */
  public function validate(mixed $value, Constraint $constraint): void {
    if (!$constraint instanceof EligibleGroupsConstraint) {
      throw new UnexpectedTypeException($constraint, EligibleGroupsConstraint::class);
    }
    if (!$value instanceof TeacherAssignment) {
      return;
    }
    $template = $value->getTemplate();
    $subject = $template?->getSubject();
    if ($subject === NULL) {
      return;
    }
    $expected = [
      'activity_type' => (string) $template->get('activity_type')->target_id,
      'specialty' => (string) $subject->get('specialty')->target_id,
      'level' => (string) $subject->get('level')->target_id,
    ];
    foreach ($value->get('groups')->referencedEntities() as $group) {
      if (!$group instanceof StudentGroup) {
        continue;
      }
      foreach ($expected as $field_name => $target_id) {
        if ((string) $group->get($field_name)->target_id !== $target_id) {
          $this->context->buildViolation($constraint->message)
            ->setParameter('%label', (string) $group->label())
            ->atPath('groups')
            ->addViolation();
          break;
        }
      }
    }
  }

}
