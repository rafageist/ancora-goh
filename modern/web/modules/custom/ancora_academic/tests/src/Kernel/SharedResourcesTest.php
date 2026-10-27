<?php

declare(strict_types=1);

namespace Drupal\Tests\ancora_academic\Kernel;

use Drupal\ancora_academic\Entity\ActivityTemplate;
use Drupal\ancora_academic\Entity\ResourceGroup;
use Drupal\ancora_academic\Export\AcademicExport;
use Drupal\ancora_academic\Export\LegacyRows;
use Drupal\ancora_academic\Hook\SharedResourceHooks;
use Drupal\ancora_academic\SharedResources;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;

/**
 * Other resources, resource groups and their resources (D9d).
 *
 * Legacy (legacy-parity-inventory.md PAR-057, PAR-058; cases 112, 142).
 */
#[CoversClass(SharedResources::class)]
#[CoversClass(SharedResourceHooks::class)]
#[Group('ancora')]
final class SharedResourcesTest extends AcademicKernelTestBase {

  /**
   * The fixture's templates, in id order.
   *
   * @return list<\Drupal\ancora_academic\Entity\ActivityTemplate>
   *   The templates.
   */
  private function templates(): array {
    $storage = $this->container->get('entity_type.manager')->getStorage('ancora_activity_template');
    return array_values($storage->loadMultiple($storage->getQuery()->accessCheck(FALSE)->sort('id')->execute()));
  }

  /**
   * Creates other resources of the owner.
   *
   * @return list<int>
   *   Their ids.
   */
  private function resources(string ...$codes): array {
    $ids = [];
    foreach ($codes as $code) {
      $resource = $this->newRecord('ancora_other_resource', ['label' => "Resource $code", 'code' => $code]);
      $this->assertSame([], $this->violationFields($resource));
      $resource->save();
      $ids[] = (int) $resource->id();
    }
    return $ids;
  }

  /**
   * The group of a template added last.
   */
  private function lastGroup(SharedResources $shared, ActivityTemplate $template): ResourceGroup {
    $groups = $shared->groups($template->id());
    $group = end($groups);
    $this->assertInstanceOf(ResourceGroup::class, $group);
    return $group;
  }

  /**
   * The editor operations with the legacy values, and the legacy rows.
   */
  public function testEditorAndLegacyRows(): void {
    $plan = $this->loadFixture();
    $shared = $this->container->get(SharedResources::class);
    [$r1, $r2, $r3] = $this->resources('R1', 'R2', 'R3');
    $template = $this->templates()[0];

    $this->assertSame([], $shared->addGroup($template));
    $g1 = $this->lastGroup($shared, $template);
    $values = [(int) $g1->get('priority')->value, (int) $g1->get('needed')->value, (bool) $g1->get('essential')->value];
    $this->assertSame([1, 1, TRUE], $values, 'the legacy window adds priority 1, needed 1, essential');
    $this->assertSame((int) $plan->id(), $g1->getPlanId());
    $this->assertSame([1], $shared->priorityOptions($template->id()), 'max 1 = one group: options 1..1');

    // The checked resources get the group's highest priority + 1, all the
    // same; the first ones 1 (PHP null + 1).
    $this->assertSame([], $shared->addMembers($g1, [$r1, $r2]));
    $this->assertSame([], $shared->addMembers($g1, [$r3]));
    $priorities = array_map(static fn ($m): int => (int) $m->get('priority')->value, array_values($shared->members($g1->id())));
    $this->assertSame([1, 1, 2], $priorities);
    $this->assertSame([], $shared->candidates($template), 'every resource of the owner is in a group of the template');

    $this->assertSame([], $shared->addGroup($template));
    $g2 = $this->lastGroup($shared, $template);
    $this->assertSame([1, 2], $shared->priorityOptions($template->id()), 'max 1 < 2 groups: options 1..2');
    $this->assertSame([], $shared->setGroup($g2, ['priority' => 2, 'needed' => 1, 'essential' => FALSE]));
    $this->assertSame([1, 2], $shared->priorityOptions($template->id()), 'max 2 = 2 groups: max lowered to 1, options 1..2');
    $this->assertNotSame([], $shared->setGroup($g2, ['needed' => 0]), 'needed is at least 1');

    $rows = $this->container->get(LegacyRows::class)->build($this->container->get(AcademicExport::class)->export($plan), 'u1');
    $this->assertSame(['R1', 'R2', 'R3'], array_column($rows['otro_recurso'], 'id'));
    $this->assertSame('u1', $rows['otro_recurso'][0]['creador']);
    $group = static fn ($g, int $priority, bool $essential): array => [
      'id' => 'g' . $g->id(),
      'plantilla' => 'A1',
      'prioridad' => $priority,
      'necesarios' => 1,
      'imprescindibles' => $essential,
      'creador' => 'u1',
    ];
    $this->assertSame([$group($g1, 1, TRUE), $group($g2, 2, FALSE)], $rows['otro_grupo_de_recursos_para_actividad']);
    $this->assertSame([
      ['grupo_de_recursos' => 'g' . $g1->id(), 'prioridad' => 1, 'otro_recurso' => 'R1'],
      ['grupo_de_recursos' => 'g' . $g1->id(), 'prioridad' => 1, 'otro_recurso' => 'R2'],
      ['grupo_de_recursos' => 'g' . $g1->id(), 'prioridad' => 2, 'otro_recurso' => 'R3'],
    ], $rows['otro_recurso_para_actividad']);
    $numbered = $this->container->get(LegacyRows::class)->build($this->container->get(AcademicExport::class)->export($plan), 'u1', TRUE);
    $this->assertSame(LegacyRows::engineId((int) $g1->id(), 'g' . $g1->id()), $numbered['otro_grupo_de_recursos_para_actividad'][0]['id']);
    $this->assertSame(LegacyRows::engineId($r1, 'R1'), $numbered['otro_recurso_para_actividad'][0]['otro_recurso']);
  }

  /**
   * Owner only; another planner's resource is refused; cascades.
   */
  public function testAccessAndCascade(): void {
    $this->loadFixture();
    $shared = $this->container->get(SharedResources::class);
    [$r1, $r2] = $this->resources('R1', 'R2');
    $template = $this->templates()[0];
    $shared->addGroup($template);
    $group = $this->lastGroup($shared, $template);
    $shared->addMembers($group, [$r1, $r2]);
    $count = fn (string $type) => (int) $this->container->get('entity_type.manager')->getStorage($type)->getQuery()->accessCheck(TRUE)->count()->execute();
    $types = ['ancora_resource_group', 'ancora_resource_member', 'ancora_other_resource'];
    $counts = fn (): array => array_map($count, $types);
    $this->assertSame([1, 2, 2], $counts());

    $other = $this->createUser(['view academic data', 'edit academic data']);
    $this->setCurrentUser($other);
    $this->assertSame([0, 0, 0], $counts());
    $this->assertFalse($group->access('update'));
    $foreign = $this->newRecord('ancora_other_resource', ['label' => 'Foreign', 'code' => 'RF']);
    $foreign->save();
    $this->setCurrentUser($this->owner);
    $this->assertNotSame([], $shared->addMembers($group, [(int) $foreign->id()]), 'another planner\'s resource is refused');
    $this->assertArrayNotHasKey((int) $foreign->id(), $shared->candidates($template), 'the candidates are the owner\'s (not every user\'s, as legacy)');

    // Deleting a resource removes it from its groups (ON DELETE CASCADE).
    $this->container->get('entity_type.manager')->getStorage('ancora_other_resource')->load($r1)->delete();
    $this->assertSame([$r2], array_values(array_map(static fn ($m): int => (int) $m->get('other_resource')->target_id, $shared->members($group->id()))));
    // Deleting the template deletes its groups and their resources (its
    // assignments first, which the academic guard requires).
    $etm = $this->container->get('entity_type.manager');
    foreach (['ancora_teacher_assignment', 'ancora_room_assignment'] as $type) {
      $etm->getStorage($type)->delete($etm->getStorage($type)->loadByProperties(['template' => $template->id()]));
    }
    $template->delete();
    $this->assertSame(0, $this->recordCount('ancora_resource_group'));
    $this->assertSame(0, $this->recordCount('ancora_resource_member'));
  }

  /**
   * The relevel of trigger nivela_prioridad_rxact_after_delete (Q-06).
   */
  public function testPriorityRelevel(): void {
    $this->loadFixture();
    $shared = $this->container->get(SharedResources::class);
    [$t1, $t2] = $this->templates();
    foreach ([1, 2, 3] as $priority) {
      $shared->addGroup($t1);
      $shared->setGroup($this->lastGroup($shared, $t1), ['priority' => $priority]);
    }
    $shared->addGroup($t2);
    $priorities = function () use ($shared, $t1): array {
      $this->container->get('entity_type.manager')->getStorage('ancora_resource_group')->resetCache();
      return array_values(array_map(static fn ($g): int => (int) $g->get('priority')->value, $shared->groups($t1->id())));
    };
    $this->assertSame([1, 2, 3], $priorities());
    // Priority 1 is still held by the other template's group: nothing moves.
    reset($shared->groups($t1->id()))->delete();
    $this->assertSame([2, 3], $priorities());
    // No group of any template keeps priority 2: the template's 3 moves to 2.
    reset($shared->groups($t1->id()))->delete();
    $this->assertSame([2], $priorities());
  }

  /**
   * The update installs the three entity types.
   */
  public function testUpdatePath(): void {
    $manager = $this->container->get('entity.definition_update_manager');
    foreach (['ancora_resource_member', 'ancora_resource_group', 'ancora_other_resource'] as $entity_type_id) {
      $manager->uninstallEntityType($manager->getEntityType($entity_type_id));
    }
    $this->container->get('module_handler')->loadInclude('ancora_academic', 'install');
    $this->assertStringContainsString('ancora_other_resource, ancora_resource_group, ancora_resource_member', ancora_academic_update_11008());
    $this->assertStringContainsString('none', ancora_academic_update_11008());
  }

}
