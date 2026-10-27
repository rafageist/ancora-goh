<?php

declare(strict_types=1);

namespace Drupal\ancora_academic;

use Drupal\ancora_academic\Entity\ActivityTemplate;
use Drupal\ancora_academic\Entity\ActivityType;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * The legacy upkeep of classification priorities and template order (D13).
 *
 * Classification priority (clasificacion_de_actividad.prioridad, PAR-019,
 * PAR-020; cases 112, 144):
 * - a new one gets the creator's count + 1 (get_form_new_clasifact.php,
 *   a hidden field); an edit keeps it;
 * - up/down swap it with the creator's previous/next one
 *   (sube_prioridad_clasif, baja_prioridad_clasif); without a neighbour
 *   nothing changes (legacy then fails on the creator's last one when
 *   another creator has more, Q-34);
 * - a delete renumbers the creator's ones 1..n in their order
 *   (arregla_prioridades_clasif).
 *
 * Template order (plantilla_de_actividad.orden within a subject and period,
 * PAR-035; cases 111, 144):
 * - a new one gets count + 1 (get_form_new_plantilla.php), and any insert
 *   renumbers the breakdown 1..n (actualiza_orden_act_after_insert);
 * - the edit form keeps the order, the subject and the period;
 * - up/down swap it with its neighbour (sube_orden_de_plantilla,
 *   baja_orden_de_plantilla); the first or last one does not move;
 * - a delete renumbers 1..n (actualiza_orden_act_after_delete).
 * The order has no gaps, which rango() needs (it looks for orders n-1 and
 * n+1). Ties when renumbering go by entity id (legacy: physical order).
 */
final class OrderUpkeep {

  /**
   * TRUE while this service saves records (the hooks then do nothing).
   */
  private bool $running = FALSE;

  public function __construct(
    private readonly EntityTypeManagerInterface $entityTypeManager,
  ) {}

  /**
   * Whether this service is saving records.
   */
  public function isRunning(): bool {
    return $this->running;
  }

  /**
   * The priority of a new classification of an owner: count + 1.
   */
  public function nextPriority(int $owner): int {
    return (int) $this->entityTypeManager->getStorage('ancora_activity_type')->getQuery()
      ->accessCheck(FALSE)->condition('uid', $owner)->count()->execute() + 1;
  }

  /**
   * The order of a new template of a subject and period: count + 1.
   */
  public function nextSequence(int|string $subject, int|string $period): int {
    return count($this->templates($subject, $period)) + 1;
  }

  /**
   * Renumbers an owner's classification priorities 1..n in their order.
   *
   * @return int
   *   The number of records changed.
   */
  public function renumberPriorities(int $owner): int {
    $storage = $this->entityTypeManager->getStorage('ancora_activity_type');
    $types = $storage->loadMultiple($storage->getQuery()->accessCheck(FALSE)->condition('uid', $owner)->execute());
    $key = static fn ($t): array => [(int) $t->get('priority')->value, (int) $t->id()];
    usort($types, static fn ($a, $b): int => $key($a) <=> $key($b));
    return $this->number($types, 'priority');
  }

  /**
   * Renumbers the templates of a subject and period 1..n in their order.
   *
   * @return int
   *   The number of records changed.
   */
  public function renumberSequences(int|string $subject, int|string $period): int {
    return $this->number($this->templates($subject, $period), 'sequence');
  }

  /**
   * Moves a classification up (-1) or down (+1) among its creator's.
   *
   * @return bool
   *   Whether it moved.
   */
  public function moveActivityType(ActivityType $type, int $direction): bool {
    $priority = (int) $type->get('priority')->value;
    $neighbour = $this->entityTypeManager->getStorage('ancora_activity_type')->getQuery()
      ->accessCheck(FALSE)
      ->condition('uid', (int) $type->getCreatorId())
      ->condition('priority', $priority + $direction)
      ->range(0, 1)
      ->execute();
    if ($neighbour === [] || $priority + $direction < 1) {
      return FALSE;
    }
    $other = $this->entityTypeManager->getStorage('ancora_activity_type')->load(reset($neighbour));
    $this->swap($type, $other, 'priority');
    return TRUE;
  }

  /**
   * Moves a template up (-1) or down (+1) within its subject and period.
   *
   * @return bool
   *   Whether it moved.
   */
  public function moveTemplate(ActivityTemplate $template, int $direction): bool {
    $subject = $template->get('subject')->target_id;
    $period = $template->get('period')->target_id;
    $this->renumberSequences($subject, $period);
    $templates = array_values($this->templates($subject, $period));
    $ids = array_map(static fn ($t): int => (int) $t->id(), $templates);
    $at = array_search((int) $template->id(), $ids, TRUE);
    if ($at === FALSE || !isset($templates[$at + $direction])) {
      return FALSE;
    }
    $this->swap($templates[$at], $templates[$at + $direction], 'sequence');
    return TRUE;
  }

  /**
   * The templates of a subject and period, by order and id.
   *
   * @return array<int, \Drupal\ancora_academic\Entity\ActivityTemplate>
   *   The templates.
   */
  private function templates(int|string $subject, int|string $period): array {
    $storage = $this->entityTypeManager->getStorage('ancora_activity_template');
    $storage->resetCache();
    $ids = $storage->getQuery()->accessCheck(FALSE)
      ->condition('subject', $subject)
      ->condition('period', $period)
      ->sort('sequence')
      ->sort('id')
      ->execute();
    return $storage->loadMultiple($ids);
  }

  /**
   * Gives records the values 1..n of a field, in their order.
   */
  private function number(array $records, string $field): int {
    $changed = 0;
    $this->running = TRUE;
    try {
      $i = 0;
      foreach ($records as $record) {
        $i++;
        if ((int) $record->get($field)->value !== $i) {
          $record->set($field, $i)->save();
          $changed++;
        }
      }
    }
    finally {
      $this->running = FALSE;
    }
    return $changed;
  }

  /**
   * Swaps the values of a field of two records.
   */
  private function swap($a, $b, string $field): void {
    $this->running = TRUE;
    try {
      $value = $a->get($field)->value;
      $a->set($field, $b->get($field)->value)->save();
      $b->set($field, $value)->save();
    }
    finally {
      $this->running = FALSE;
    }
  }

}
