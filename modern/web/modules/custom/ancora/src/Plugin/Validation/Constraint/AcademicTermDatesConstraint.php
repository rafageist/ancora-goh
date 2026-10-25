<?php

declare(strict_types=1);

namespace Drupal\ancora\Plugin\Validation\Constraint;

use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Validation\Attribute\Constraint;
use Symfony\Component\Validator\Constraint as SymfonyConstraint;

/**
 * The last day of an academic term is not before its first day.
 */
#[Constraint(
  id: 'AncoraAcademicTermDates',
  label: new TranslatableMarkup('Academic term dates', [], ['context' => 'Validation']),
  type: ['entity:ancora_academic_term'],
)]
final class AcademicTermDatesConstraint extends SymfonyConstraint {

  /**
   * The violation message.
   */
  public string $message = 'The last day (%end) must not be before the first day (%start).';

}
