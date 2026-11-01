<?php

declare(strict_types=1);

namespace Drupal\ancora_academic\Plugin\Validation\Constraint;

use Drupal\ancora_academic\Entity\Period;
use Drupal\ancora_academic\Entity\TimeRestriction;
use Drupal\ancora_academic\TimeRestrictionGrid;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Validates the AncoraRestrictionCell constraint.
 */
final class RestrictionCellConstraintValidator extends ConstraintValidator implements ContainerInjectionInterface {

  public function __construct(
    private readonly TimeRestrictionGrid $grid,
  ) {}

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): static {
    return new static($container->get(TimeRestrictionGrid::class));
  }

  /**
   * {@inheritdoc}
   */
  public function validate(mixed $value, Constraint $constraint): void {
    if (!$constraint instanceof RestrictionCellConstraint) {
      throw new UnexpectedTypeException($constraint, RestrictionCellConstraint::class);
    }
    if (!$value instanceof TimeRestriction || !array_key_exists($value->getKind(), TimeRestriction::KINDS)) {
      return;
    }
    $kind = $value->getKind();
    $own = TimeRestriction::KINDS[$kind];
    foreach (array_keys(TimeRestriction::TARGET_TYPES) as $field) {
      if (($field === $own) === $value->get($field)->isEmpty()) {
        $this->context->buildViolation($constraint->targetMessage)->atPath($own ?? $field)->addViolation();
        return;
      }
    }
    $period = $value->get('period')->entity;
    if (!$period instanceof Period) {
      return;
    }
    [$day, $slot] = $value->getCell();
    $target = $value->getTarget();
    $params = [
      '%day' => $day,
      '%slot' => $slot,
      '%object' => $target === NULL ? (string) new TranslatableMarkup('the period') : (string) $target->label(),
    ];
    if ($this->grid->exists($kind, $value->getTargetId(), (int) $period->id(), $day, $slot, $value->isNew() ? NULL : (int) $value->id())) {
      $this->context->buildViolation($constraint->duplicateMessage, $params)->atPath('day')->addViolation();
      return;
    }
    if (in_array($kind, TimeRestriction::OCCUPIED, TRUE) && $value->isNew()) {
      $occupied = $this->grid->occupied($kind, (int) $value->getTargetId(), $period, $day, $slot);
      if ($occupied !== FALSE) {
        $this->context->buildViolation($occupied === NULL ? $constraint->unknownMessage : $constraint->occupiedMessage, $params)->atPath('day')->addViolation();
      }
    }
  }

}
