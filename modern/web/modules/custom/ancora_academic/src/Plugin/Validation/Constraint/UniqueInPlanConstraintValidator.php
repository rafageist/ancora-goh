<?php

declare(strict_types=1);

namespace Drupal\ancora_academic\Plugin\Validation\Constraint;

use Drupal\ancora_academic\AcademicEntityInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Validates the AncoraUniqueInPlan constraint.
 */
final class UniqueInPlanConstraintValidator extends ConstraintValidator implements ContainerInjectionInterface {

  public function __construct(
    private readonly EntityTypeManagerInterface $entityTypeManager,
  ) {}

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): static {
    return new static($container->get('entity_type.manager'));
  }

  /**
   * {@inheritdoc}
   */
  public function validate(mixed $value, Constraint $constraint): void {
    if (!$constraint instanceof UniqueInPlanConstraint) {
      throw new UnexpectedTypeException($constraint, UniqueInPlanConstraint::class);
    }
    if (!$value instanceof AcademicEntityInterface || !$value->hasField('plan')) {
      return;
    }
    $plan_id = $value->getPlanId();
    if ($plan_id === NULL) {
      return;
    }

    $query = $this->entityTypeManager->getStorage($value->getEntityTypeId())->getQuery()
      ->accessCheck(FALSE)
      ->condition('plan', $plan_id)
      ->range(0, 1);
    foreach ($constraint->fields as $field_name) {
      $item = $value->get($field_name)->first();
      $main = $item?->mainPropertyName();
      $field_value = $main === NULL ? NULL : $item->get($main)->getValue();
      // Missing values are reported by the required-field constraints.
      if ($field_value === NULL || $field_value === '') {
        return;
      }
      $query->condition($field_name, $field_value);
    }
    if (!$value->isNew()) {
      $query->condition($value->getEntityType()->getKey('id'), $value->id(), '<>');
    }
    if ($query->execute()) {
      $last = end($constraint->fields);
      $this->context->buildViolation($constraint->message)
        ->setParameter('%value', (string) $value->get($last)->getString())
        ->atPath($last)
        ->addViolation();
    }
  }

}
