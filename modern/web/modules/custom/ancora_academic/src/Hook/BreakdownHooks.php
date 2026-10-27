<?php

declare(strict_types=1);

namespace Drupal\ancora_academic\Hook;

use Drupal\ancora_academic\BreakdownMaintainer;
use Drupal\ancora_academic\Entity\Period;
use Drupal\ancora_academic\Entity\Plan;
use Drupal\ancora_academic\Entity\Subject;
use Drupal\Core\Entity\Display\EntityViewDisplayInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Url;

/**
 * The legacy breakdown triggers and cascades (BreakdownMaintainer).
 */
final class BreakdownHooks {

  public function __construct(
    private readonly BreakdownMaintainer $maintainer,
  ) {}

  /**
   * Implements hook_entity_insert(): the legacy insert triggers.
   */
  #[Hook('entity_insert')]
  public function entityInsert(EntityInterface $entity): void {
    if ($entity instanceof Subject) {
      $this->maintainer->ensureForSubject($entity);
    }
    elseif ($entity instanceof Period) {
      $this->maintainer->ensureForPeriod($entity);
    }
  }

  /**
   * Implements hook_entity_update(): a new owner or plan makes new pairs.
   */
  #[Hook('entity_update')]
  public function entityUpdate(EntityInterface $entity): void {
    $original = $entity->getOriginal();
    if ($entity instanceof Subject && $original instanceof Subject && $original->getCreatorId() !== $entity->getCreatorId()) {
      $this->maintainer->ensureForSubject($entity);
    }
    elseif ($entity instanceof Plan && $original instanceof Plan && $original->getCreatorId() !== $entity->getCreatorId()) {
      foreach ($this->maintainer->periodsOf($entity) as $period) {
        $this->maintainer->ensureForPeriod($period);
      }
    }
    elseif ($entity instanceof Period && $original instanceof Period && $original->getPlanId() !== $entity->getPlanId()) {
      foreach ($this->maintainer->ofPeriod($entity->id()) as $breakdown) {
        // The breakdown follows the plan of its period (Breakdown::preSave).
        $breakdown->save();
      }
      $this->maintainer->ensureForPeriod($entity);
    }
  }

  /**
   * Implements hook_ENTITY_TYPE_view() for subjects and periods.
   *
   * Links the page of a subject or period to its breakdowns, as the legacy
   * subject browser lists the breakdown of each period with its templates
   * (desglose_detalles.tpl).
   */
  #[Hook('ancora_subject_view')]
  #[Hook('ancora_period_view')]
  public function view(array &$build, EntityInterface $entity, EntityViewDisplayInterface $display, string $view_mode): void {
    if ($view_mode !== 'full' && $view_mode !== 'default') {
      return;
    }
    $field = $entity instanceof Subject ? 'subject' : 'period';
    $build['ancora_breakdowns'] = [
      '#type' => 'link',
      '#title' => new TranslatableMarkup('Subject breakdowns (scheduling options)'),
      '#url' => Url::fromRoute('entity.ancora_breakdown.collection', [], ['query' => [$field => $entity->id()]]),
      '#weight' => 100,
      '#prefix' => '<p class="ancora-breakdowns-link">',
      '#suffix' => '</p>',
    ];
  }

  /**
   * Implements hook_entity_predelete(): ON DELETE CASCADE.
   */
  #[Hook('entity_predelete')]
  public function entityPredelete(EntityInterface $entity): void {
    $field = match (TRUE) {
      $entity instanceof Subject => 'subject',
      $entity instanceof Period => 'period',
      $entity instanceof Plan => 'plan',
      default => NULL,
    };
    if ($field !== NULL) {
      $this->maintainer->deleteFor($field, $entity->id());
    }
  }

}
