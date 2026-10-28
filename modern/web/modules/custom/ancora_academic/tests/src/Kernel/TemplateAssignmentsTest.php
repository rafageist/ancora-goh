<?php

declare(strict_types=1);

namespace Drupal\Tests\ancora_academic\Kernel;

use Drupal\ancora_academic\Entity\ActivityTemplate;
use Drupal\ancora_academic\Export\AcademicExport;
use Drupal\ancora_academic\Export\LegacyRows;
use Drupal\ancora_academic\OrderUpkeep;
use Drupal\ancora_academic\Entity\Plan;
use Drupal\ancora_academic\TemplateAssignments;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;

/**
 * The operations of the assignment editor and what the engine receives.
 *
 * Legacy (legacy-parity-inventory.md PAR-051, 052, 055; #122).
 */
#[CoversClass(TemplateAssignments::class)]
#[Group('ancora')]
final class TemplateAssignmentsTest extends AcademicKernelTestBase {

  /**
   * The fixture's template of a code (subject code + number).
   */
  private function template(string $code): ActivityTemplate {
    $subject = $this->byCode('ancora_subject', substr($code, 0, 1));
    $storage = $this->container->get('entity_type.manager')->getStorage('ancora_activity_template');
    $storage->resetCache();
    foreach ($storage->loadByProperties(['subject' => $subject->id()]) as $template) {
      if ((int) $template->get('sequence')->value === (int) substr($code, 1)) {
        return $template;
      }
    }
    $this->fail("No template $code.");
  }

  /**
   * The engine rows of a table for one template.
   */
  private function rows(Plan $plan, string $table, ?string $template = NULL): array {
    $rows = $this->container->get(LegacyRows::class)->build($this->container->get(AcademicExport::class)->export($plan), 'u1')[$table] ?? [];
    return array_values(array_filter($rows, static fn (array $row): bool => $template === NULL || ($row['plantilla'] ?? NULL) === $template));
  }

  /**
   * Labels of records keyed by id.
   */
  private function labels(array $records): array {
    return array_values(array_map(static fn ($r): string => (string) $r->label(), $records));
  }

  /**
   * Assign, prioritize, group, remove and undo, as the engine receives them.
   */
  public function testOperationsAndEngineRows(): void {
    $plan = $this->loadFixture();
    $service = $this->container->get(TemplateAssignments::class);
    $a1 = $this->template('A1');
    $tb = (int) $this->byCode('ancora_teacher', 'TB')->id();
    $g1 = (int) $this->byCode('ancora_student_group', 'G1', $plan)->id();
    $g2 = (int) $this->byCode('ancora_student_group', 'G2', $plan)->id();

    $this->assertSame(['Teacher Beta'], $this->labels($service->candidates($a1, 'teacher')), 'the owner\'s teachers that A1 does not have');
    $this->assertSame([], $service->candidates($a1, 'room'), 'A1 has both rooms');
    $this->assertSame([$g1, $g2], array_keys($service->groups($a1)), 'DameGruposDePlantilla');
    $this->assertSame(20, $service->enrollment($service->groups($a1)[$g1]), 'a group\'s enrolment is the sum of its cohorts\' (one cohort of 20)');
    $this->assertSame([1], $service->priorityOptions($a1, 'teacher'), 'max 1 = 1 teacher: 1..1');
    $state = $service->state($a1);

    // Assign: priority 1, no group yet (add_ProfesorParaActividad).
    [$messages, $assignment] = $service->assign($a1, 'teacher', $tb);
    $this->assertSame([], $messages);
    $this->assertSame([1, []], [$assignment->getPriority(), $assignment->getGroupIds()]);
    $this->assertNotSame($state, $service->state($a1), 'the state token follows the changes');
    $this->assertSame([1, 2], $service->priorityOptions($a1, 'teacher'), 'max 1 < 2 teachers: 1..2');
    $this->assertSame(['This teacher is already assigned to this activity template.'], $service->assign($a1, 'teacher', $tb)[0]);

    // Priority among the offered values; ties allowed, nothing else.
    $this->assertSame([], $service->setPriority($a1, $assignment, 2));
    $this->assertSame([1, 2], $service->priorityOptions($a1, 'teacher'), 'max 2 = 2 teachers: 1..2');
    $this->assertSame(['The priority must be one of 1, 2.'], $service->setPriority($a1, $assignment, 3));
    $this->assertSame(['The priority must be one of 1, 2.'], $service->setPriority($a1, $assignment, '1 OR 1=1'));

    // Groups among the template's (form_edit_pxact.tpl).
    $this->assertSame([], $service->setGroups($a1, $assignment, [$g2]));
    $this->assertSame([
      'That group does not attend this activity template.',
    ], $service->setGroups($a1, $assignment, [$g2, 999999]));
    $this->assertSame([$g2], $service->assignment($a1, 'teacher', $assignment->id())->getGroupIds());

    // What the engine receives: the priorities and groups of A1.
    $teachers = array_map(static fn (array $r): array => [$r['profesor'], $r['prioridad']], $this->rows($plan, 'profesor_para_actividad', 'A1'));
    $this->assertSame([['TA', 1], ['TB', 2]], $teachers);
    $tb_row = array_values(array_filter($this->rows($plan, 'profesor_para_actividad', 'A1'), static fn (array $r): bool => $r['profesor'] === 'TB'))[0];
    $groups = array_column(array_filter($this->rows($plan, 'profesor_para_grupo'), static fn (array $r): bool => $r['profesor_para_actividad'] === $tb_row['id']), 'grupo');
    $this->assertSame(['G2'], array_values($groups));

    // Rooms: the priority relevel of RoomPriorityHooks applies.
    $rooms = $service->assignments($a1, 'room');
    [$l1, $l2] = array_values($rooms);
    $this->assertSame([1, 2], $service->priorityOptions($a1, 'room'), 'max 2 = 2 rooms: 1..2');
    $this->assertSame([], $service->setPriority($a1, $l2, 1), 'a tie');
    $this->assertSame([['L1', 1], ['L2', 1]], array_map(static fn (array $r): array => [$r['lugar'], $r['prioridad']], $this->rows($plan, 'lugar_para_actividad', 'A1')));
    $this->assertSame([], $service->setPriority($a1, $l2, 2));

    // Remove, then undo with the same priority and groups.
    $snapshot = $service->remove($service->assignment($a1, 'teacher', $assignment->id()));
    $this->assertSame([
      'kind' => 'teacher',
      'template' => (int) $a1->id(),
      'resource' => $tb,
      'priority' => 2,
      'groups' => [$g2],
      'label' => 'Teacher Beta',
    ], $snapshot);
    $this->assertSame(['Teacher Beta'], $this->labels($service->candidates($a1, 'teacher')));
    $this->assertSame([], $service->restore($a1, $snapshot));
    $this->assertSame([['TA', 1], ['TB', 2]], array_map(static fn (array $r): array => [$r['profesor'], $r['prioridad']], $this->rows($plan, 'profesor_para_actividad', 'A1')));
    $this->assertSame(['There is nothing to undo.'], $service->restore($this->template('A2'), $snapshot), 'the snapshot of another template');

    $snapshot = $service->remove($l1);
    $this->assertSame([['L2', 1]], array_map(static fn (array $r): array => [$r['lugar'], $r['prioridad']], $this->rows($plan, 'lugar_para_actividad', 'A1')), 'the gap is closed (nivela_prioridad_lxact)');
    $this->assertSame([], $service->restore($a1, $snapshot));
    $this->assertSame([['L1', 1], ['L2', 1]], array_map(static fn (array $r): array => [$r['lugar'], $r['prioridad']], $this->rows($plan, 'lugar_para_actividad', 'A1')), 'undo after a relevel ties');

    // The template order arrows of the editor's list are D13's: A2 up. The
    // engine ids of templates follow their order (subject code + order), so
    // Teacher Beta's assignment, on the moved A1, now reaches the engine on
    // the template of order 2.
    $a2 = $this->template('A2');
    $plantilla = fn (): array => array_column(array_filter($this->rows($plan, 'profesor_para_actividad'), static fn (array $r): bool => $r['profesor'] === 'TB'), 'plantilla');
    $this->assertSame(['A1', 'B1', 'B2'], $plantilla());
    $this->assertTrue($this->container->get(OrderUpkeep::class)->moveTemplate($a2, -1));
    $storage = $this->container->get('entity_type.manager')->getStorage('ancora_activity_template');
    $storage->resetCache();
    $this->assertSame([2, 1], [
      (int) $storage->load($a1->id())->get('sequence')->value,
      (int) $storage->load($a2->id())->get('sequence')->value,
    ]);
    $this->assertSame(['A2', 'B1', 'B2'], $plantilla(), 'the engine receives the moved order');
    $orders = array_column(array_filter($this->rows($plan, 'plantilla_de_actividad'), static fn (array $r): bool => str_starts_with($r['desglose'], 'A@')), 'orden');
    sort($orders);
    $this->assertSame([1, 2], $orders, 'no gap');
  }

  /**
   * Another planner's records and other templates' assignments are refused.
   */
  public function testOwnerBoundary(): void {
    $this->loadFixture();
    $service = $this->container->get(TemplateAssignments::class);
    $a1 = $this->template('A1');
    $b1 = $this->template('B1');
    $other = $this->createUser(['view academic data', 'edit academic data']);
    $this->setCurrentUser($other);
    $foreign = $this->newRecord('ancora_teacher', ['label' => 'Foreign teacher', 'code' => 'TF']);
    $foreign->save();
    $foreign_room = $this->newRecord('ancora_room', ['label' => 'Foreign room', 'code' => 'LF', 'capacity' => 0]);
    $foreign_room->save();
    $this->assertFalse($a1->access('update'), 'another planner cannot edit the template');
    $this->setCurrentUser($this->owner);

    $this->assertSame(['That teacher is not one of the teachers of the owner of this template.'], $service->assign($a1, 'teacher', $foreign->id())[0]);
    $this->assertSame(['That room is not one of the rooms of the owner of this template.'], $service->assign($a1, 'room', $foreign_room->id())[0]);
    $this->assertSame(['That record does not exist any more.'], $service->assign($a1, 'teacher', 999999)[0]);
    $this->assertSame(['That record does not exist any more.'], $service->assign($a1, 'teacher', '1; DROP')[0]);
    $this->assertNotContains('Foreign teacher', $this->labels($service->candidates($a1, 'teacher')));

    $b1_assignment = array_key_first($service->assignments($b1, 'teacher'));
    $this->assertNull($service->assignment($a1, 'teacher', $b1_assignment), 'B1\'s assignment is not one of A1');
    $this->assertNull($service->assignment($a1, 'teacher', 'x'));
    $this->assertNull($service->assignment($a1, 'bogus', $b1_assignment));
  }

}
