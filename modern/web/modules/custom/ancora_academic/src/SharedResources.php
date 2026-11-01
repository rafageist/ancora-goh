<?php

declare(strict_types=1);

namespace Drupal\ancora_academic;

use Drupal\ancora_academic\Entity\ActivityTemplate;
use Drupal\ancora_academic\Entity\ResourceGroup;
use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * The other resources of an activity template (D9d, PAR-057).
 *
 * The operations of the legacy resource window of a template
 * (www/js/app/server/ancoragoh_server_recursos.js:68-120), with the same
 * values:
 * - add a group: priority 1, needed 1, essential (add_OtroGrupoDe...);
 * - set the priority of a group, 1..max + 1 (priorityOptions());
 * - set the needed resources, 1..(resources in the group), and the
 *   essential flag (set_GrupoRecursos_necesarios, _imprescindibles);
 * - add resources: the checked resources of the owner that no group of
 *   the template has (dame_otros_recursos_sin_asignar), all with the
 *   group's highest resource priority + 1 (form_rxact.tpl);
 * - remove a resource from a group; delete a group (its resources go with
 *   it; the priority relevel is SharedResourceHooks).
 *
 * The legacy candidate list is not filtered by owner: it offers every
 * user's resources (dame_otros_recursos_sin_asignar reads all of
 * otro_recurso). Like Q-12, a security defect: recorded, not reproduced;
 * the candidates are the template owner's resources (D1-D3).
 *
 * What generar() does with the rows (case 142): posible_inicio() never
 * finds a resource unavailable (6.3, 6.13); a group whose "needed" is more
 * than its resources blocks the template when it is essential and not the
 * template's last group. The resources of a placed activity are chosen by
 * generar.sql:1366-1393, ORDER BY resource priority, use, travel cost, from
 * every group but the LAST one (in entry order; the loop starts at index
 * 0, which is empty), each taking as many as the "needed" of one group
 * (gruporecursos[i]), with no availability check: a resource that is
 * restricted or booked at the chosen cell fails the run with 23505 and
 * keeps nothing (D-05, case 083). The group priority is never read.
 */
final class SharedResources {

  /**
   * The references of each type, for the cascades.
   */
  private const REFERENCES = [
    'ancora_resource_group' => ['plan' => 'ancora_plan', 'template' => 'ancora_activity_template'],
    'ancora_resource_member' => [
      'plan' => 'ancora_plan',
      'resource_group' => 'ancora_resource_group',
      'other_resource' => 'ancora_other_resource',
    ],
  ];

  public function __construct(
    private readonly EntityTypeManagerInterface $entityTypeManager,
    private readonly Connection $database,
  ) {}

  /**
   * The groups of a template, in id order (legacy ORDER BY id).
   *
   * @return array<int, \Drupal\ancora_academic\Entity\ResourceGroup>
   *   Groups by id.
   */
  public function groups(int|string $template_id): array {
    return $this->load('ancora_resource_group', ['template' => $template_id]);
  }

  /**
   * The resources of a group, in id order (legacy entry order).
   *
   * @return array<int, \Drupal\Core\Entity\EntityInterface>
   *   Members by id.
   */
  public function members(int|string $group_id): array {
    return $this->load('ancora_resource_member', ['resource_group' => $group_id]);
  }

  /**
   * The priorities the legacy window offers for a group of a template.
   *
   * Custom_getui_EditarPrioridadRxact.php: 1..max + 1, where max is the
   * highest group priority of the template, lowered by one when it equals
   * the number of groups.
   *
   * @return list<int>
   *   The priorities.
   */
  public function priorityOptions(int|string $template_id): array {
    $groups = $this->groups($template_id);
    $max = 0;
    foreach ($groups as $group) {
      $max = max($max, (int) $group->get('priority')->value);
    }
    if ($max === count($groups)) {
      $max--;
    }
    return range(1, max(1, $max + 1));
  }

  /**
   * The owner's resources that no group of the template has, in entry order.
   *
   * The legacy list (dame_otros_recursos_sin_asignar) is in table order, and
   * the checked resources are added in list order: with equal priorities
   * the engine takes them in that order.
   *
   * @return array<int, string>
   *   Id => label.
   */
  public function candidates(ActivityTemplate $template): array {
    $assigned = [];
    foreach ($this->groups($template->id()) as $group) {
      foreach ($this->members($group->id()) as $member) {
        $assigned[(int) $member->get('other_resource')->target_id] = TRUE;
      }
    }
    $storage = $this->entityTypeManager->getStorage('ancora_other_resource');
    $ids = $storage->getQuery()->accessCheck(FALSE)->condition('uid', (int) $template->getCreatorId())->sort('id')->execute();
    $options = [];
    foreach ($storage->loadMultiple($ids) as $id => $resource) {
      if (!isset($assigned[(int) $id])) {
        $options[(int) $id] = (string) $resource->label();
      }
    }
    return $options;
  }

  /**
   * Adds a group to a template with the legacy values.
   *
   * @return list<string>
   *   The violation messages; nothing is saved when there is any.
   */
  public function addGroup(ActivityTemplate $template): array {
    $group = $this->entityTypeManager->getStorage('ancora_resource_group')->create([
      'template' => $template->id(),
      'plan' => $template->getPlanId(),
      'priority' => 1,
      'needed' => 1,
      'essential' => TRUE,
    ]);
    return $this->save([$group]);
  }

  /**
   * Sets the priority, needed resources or essential flag of a group.
   *
   * @param \Drupal\ancora_academic\Entity\ResourceGroup $group
   *   The group.
   * @param array{priority?: int, needed?: int, essential?: bool} $values
   *   The values to set.
   *
   * @return list<string>
   *   The violation messages; nothing is saved when there is any.
   */
  public function setGroup(ResourceGroup $group, array $values): array {
    foreach (array_intersect_key($values, array_flip(['priority', 'needed', 'essential'])) as $field => $value) {
      $group->set($field, $value);
    }
    return $this->save([$group]);
  }

  /**
   * Adds resources to a group, all with its highest priority + 1.
   *
   * @param \Drupal\ancora_academic\Entity\ResourceGroup $group
   *   The group.
   * @param list<int> $resource_ids
   *   The resources, in the order of the legacy list (entry order).
   *
   * @return list<string>
   *   The violation messages; nothing is saved when there is any.
   */
  public function addMembers(ResourceGroup $group, array $resource_ids): array {
    $max = NULL;
    foreach ($this->members($group->id()) as $member) {
      $max = max($max ?? PHP_INT_MIN, (int) $member->get('priority')->value);
    }
    $storage = $this->entityTypeManager->getStorage('ancora_resource_member');
    $records = [];
    foreach (array_unique($resource_ids) as $resource) {
      $records[] = $storage->create([
        'resource_group' => $group->id(),
        'plan' => $group->getPlanId(),
        'other_resource' => (int) $resource,
        // PHP: NULL + 1 is 1 for a group without resources.
        'priority' => ($max ?? 0) + 1,
      ]);
    }
    return $this->save($records);
  }

  /**
   * Deletes the records of a plan, template, group or resource (cascade).
   */
  public function deleteFor(string $referenced_type, int|string $id): void {
    foreach (self::REFERENCES as $entity_type_id => $fields) {
      foreach ($fields as $field => $target_type) {
        if ($target_type !== $referenced_type) {
          continue;
        }
        $records = $this->load($entity_type_id, [$field => $id]);
        if ($records !== []) {
          $this->entityTypeManager->getStorage($entity_type_id)->delete($records);
        }
      }
    }
  }

  /**
   * Validates and saves records, all or nothing.
   *
   * @param list<\Drupal\Core\Entity\ContentEntityInterface> $records
   *   The records.
   *
   * @return list<string>
   *   The violation messages.
   */
  private function save(array $records): array {
    $messages = [];
    foreach ($records as $record) {
      foreach ($record->validate() as $violation) {
        $messages[] = strip_tags((string) $violation->getMessage());
      }
    }
    if ($messages !== []) {
      return array_values(array_unique($messages));
    }
    $transaction = $this->database->startTransaction();
    try {
      array_walk($records, static fn (EntityInterface $record) => $record->save());
    }
    catch (\Throwable $e) {
      $transaction->rollBack();
      throw $e;
    }
    return [];
  }

  /**
   * Loads records by field values, without access checks, in id order.
   *
   * @return array<int|string, \Drupal\Core\Entity\EntityInterface>
   *   The records.
   */
  private function load(string $entity_type_id, array $conditions): array {
    $storage = $this->entityTypeManager->getStorage($entity_type_id);
    $query = $storage->getQuery()->accessCheck(FALSE)->sort('id');
    foreach ($conditions as $field => $value) {
      $query->condition($field, $value);
    }
    return $storage->loadMultiple($query->execute());
  }

}
