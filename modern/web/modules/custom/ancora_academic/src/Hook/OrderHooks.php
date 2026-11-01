<?php

declare(strict_types=1);

namespace Drupal\ancora_academic\Hook;

use Drupal\ancora_academic\Entity\ActivityTemplate;
use Drupal\ancora_academic\Entity\ActivityType;
use Drupal\ancora_academic\OrderUpkeep;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Hook\Attribute\Hook;

/**
 * The legacy order triggers, on the records (D13; see OrderUpkeep).
 */
final class OrderHooks {

  public function __construct(
    private readonly OrderUpkeep $order,
  ) {}

  /**
   * Implements hook_ENTITY_TYPE_presave(): count + 1 for a new record.
   */
  #[Hook('ancora_activity_type_presave')]
  #[Hook('ancora_activity_template_presave')]
  public function presave(EntityInterface $entity): void {
    if (!$entity->isNew() || $this->order->isRunning()) {
      return;
    }
    if ($entity instanceof ActivityType && $entity->get('priority')->isEmpty()) {
      $entity->set('priority', $this->order->nextPriority((int) $entity->getCreatorId()));
    }
    if ($entity instanceof ActivityTemplate && $entity->get('sequence')->isEmpty()) {
      $entity->set('sequence', $this->order->nextSequence((int) $entity->get('subject')->target_id, (int) $entity->get('period')->target_id));
    }
  }

  /**
   * Implements hook_ENTITY_TYPE_insert(): the breakdown has no gaps.
   */
  #[Hook('ancora_activity_template_insert')]
  public function templateInsert(EntityInterface $entity): void {
    if (!$this->order->isRunning()) {
      $this->order->renumberSequences($entity->get('subject')->target_id, $entity->get('period')->target_id);
    }
  }

  /**
   * Implements hook_ENTITY_TYPE_update(): a changed order is renumbered.
   *
   * Legacy actualiza_orden_act_after_update renumbers the template's
   * (new) breakdown when its order changed. The forms never change it;
   * the moves go through OrderUpkeep.
   */
  #[Hook('ancora_activity_template_update')]
  public function templateUpdate(EntityInterface $entity): void {
    $original = $entity->getOriginal();
    if ($this->order->isRunning() || !$original instanceof ActivityTemplate || $original->getSequence() === (int) $entity->get('sequence')->value) {
      return;
    }
    $this->order->renumberSequences($entity->get('subject')->target_id, $entity->get('period')->target_id);
  }

  /**
   * Implements hook_ENTITY_TYPE_delete(): renumbered after a delete.
   */
  #[Hook('ancora_activity_type_delete')]
  #[Hook('ancora_activity_template_delete')]
  public function delete(EntityInterface $entity): void {
    if ($entity instanceof ActivityType) {
      $this->order->renumberPriorities((int) $entity->getCreatorId());
    }
    elseif ($entity instanceof ActivityTemplate) {
      $subject = $entity->get('subject')->target_id;
      $period = $entity->get('period')->target_id;
      if ($subject !== NULL && $period !== NULL) {
        $this->order->renumberSequences($subject, $period);
      }
    }
  }

}
