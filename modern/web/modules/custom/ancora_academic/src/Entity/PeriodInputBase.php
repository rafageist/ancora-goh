<?php

declare(strict_types=1);

namespace Drupal\ancora_academic\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Base class of the engine inputs of one period (D9b).
 *
 * Breaks, priority zones and daily load limits: records of a period,
 * entered with a grid or table editor of the period (PeriodInputs), owned
 * through the period's plan (D1-D3) and deleted with the period, as the
 * legacy rows cascade with periodo.
 */
abstract class PeriodInputBase extends AcademicEntityBase {

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type): array {
    $fields = parent::baseFieldDefinitions($entity_type);
    $fields['period'] = static::referenceField('ancora_period', new TranslatableMarkup('Period'), new TranslatableMarkup('The period.'), -10)->setRequired(TRUE);
    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public function preSave(EntityStorageInterface $storage): void {
    parent::preSave($storage);
    $period = $this->get('period')->entity;
    if ($period instanceof Period && $period->getPlanId() !== NULL) {
      $this->set('plan', $period->getPlanId());
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function listColumns(): array {
    return ['label', 'plan', 'period'];
  }

  /**
   * The period's code, for labels.
   */
  protected function periodCode(): string {
    $period = $this->get('period')->entity;
    return $period instanceof Period ? $period->getCode() : '?';
  }

}
