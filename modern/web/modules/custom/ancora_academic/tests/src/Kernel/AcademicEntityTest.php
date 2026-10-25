<?php

declare(strict_types=1);

namespace Drupal\Tests\ancora_academic\Kernel;

use Drupal\ancora_academic\AcademicEntityTypes;
use Drupal\ancora_academic\Entity\AcademicEntityBase;
use Drupal\ancora_academic\Entity\ActivityTemplate;
use Drupal\ancora_academic\Entity\Period;
use Drupal\ancora_academic\Entity\TeacherAssignment;
use Drupal\ancora_academic\ReferenceGuard;
use Drupal\Core\Entity\EntityStorageException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;

/**
 * Tests the academic entities: storage, validation and deletion guard.
 */
#[CoversClass(AcademicEntityBase::class)]
#[CoversClass(ReferenceGuard::class)]
#[Group('ancora')]
final class AcademicEntityTest extends AcademicKernelTestBase {

  /**
   * The fixture is stored and every record loads with its values.
   */
  public function testCreateReadUpdate(): void {
    $plan = $this->loadFixture();
    $expected = [
      'ancora_plan' => 1,
      'ancora_period' => 1,
      'ancora_activity_type' => 1,
      'ancora_specialty' => 1,
      'ancora_level' => 1,
      'ancora_cohort' => 2,
      'ancora_student_group' => 2,
      'ancora_subject' => 2,
      'ancora_activity_template' => 4,
      'ancora_room' => 2,
      'ancora_teacher' => 2,
      'ancora_teacher_assignment' => 4,
      'ancora_room_assignment' => 8,
    ];
    $this->assertSame(AcademicEntityTypes::ids(), array_keys($expected));
    foreach ($expected as $entity_type_id => $count) {
      $this->assertSame($count, $this->recordCount($entity_type_id), $entity_type_id);
    }

    $period = $this->byCode('ancora_period', 'PER', $plan);
    $this->assertInstanceOf(Period::class, $period);
    $this->assertSame((int) $plan->id(), $period->getPlanId());
    $this->assertSame(5, $period->getDays());
    $this->assertSame(4, $period->getSlotsPerDay());
    $this->assertSame(8.0, $period->getStartHour());
    $this->assertSame(1.0, $period->getSlotDuration());
    $this->assertSame([1], $period->getPlanDays());
    $this->assertSame((int) $plan->id(), $plan->getPlanId());

    $templates = $this->container->get('entity_type.manager')->getStorage('ancora_activity_template')
      ->loadByProperties(['sequence' => 2, 'subject' => $this->byCode('ancora_subject', 'A', $plan)->id()]);
    $template = reset($templates);
    $this->assertInstanceOf(ActivityTemplate::class, $template);
    $this->assertSame('Subject Alpha #2 (PER)', $template->label());
    $this->assertTrue($template->requiresTeacher());
    $this->assertTrue($template->requiresRoom());

    $teacher = $this->byCode('ancora_teacher', 'TA', $plan);
    $teacher->set('label', 'Teacher Alpha Prime')->save();
    $this->assertSame('Teacher Alpha Prime', $this->byCode('ancora_teacher', 'TA', $plan)->label());
    $this->assertGreaterThan(0, $teacher->getChangedTime());
  }

  /**
   * Required fields and numeric ranges are enforced.
   */
  public function testRequiredFieldsAndRanges(): void {
    $plan = $this->loadFixture();
    $this->assertSame(['code', 'label', 'plan'], $this->violationFields($this->newRecord('ancora_teacher', [])));
    $this->assertSame(['activity_type', 'period', 'plan', 'subject'], $this->violationFields($this->newRecord('ancora_activity_template', [])));

    $period = fn (array $values) => $this->newRecord('ancora_period', $values + [
      'plan' => $plan->id(),
      'label' => 'Other week',
      'code' => 'PER2',
      'days' => 5,
      'slots_per_day' => 6,
      'start_hour' => 8,
      'slot_duration' => 1,
    ]);
    $this->assertSame([], $this->violationFields($period([])));
    $this->assertSame(['days'], $this->violationFields($period(['days' => 0])));
    $this->assertSame(['slots_per_day'], $this->violationFields($period(['slots_per_day' => 0])));
    $this->assertSame(['slot_duration'], $this->violationFields($period(['slot_duration' => 0])));
    $this->assertSame(['start_hour'], $this->violationFields($period(['start_hour' => 25])));
    $this->assertSame(['plan_days'], $this->violationFields($period(['plan_days' => [0]])));

    $type = fn (array $values) => $this->newRecord('ancora_activity_type', $values + [
      'plan' => $plan->id(),
      'label' => 'Lab',
      'code' => 'C2',
    ]);
    $this->assertSame([], $this->violationFields($type([])));
    $this->assertSame(['duration_slots'], $this->violationFields($type(['duration_slots' => 0])));
    $this->assertSame(['priority'], $this->violationFields($type(['priority' => 0])));
    $this->assertSame(['cost'], $this->violationFields($type(['cost' => -1])));

    $this->assertSame(['capacity'], $this->violationFields($this->newRecord('ancora_room', [
      'plan' => $plan->id(),
      'label' => 'Broken room',
      'code' => 'L9',
      'capacity' => -1,
    ])));
    $this->assertSame(['code'], $this->violationFields($this->newRecord('ancora_teacher', [
      'plan' => $plan->id(),
      'label' => 'Spaced code',
      'code' => 'T 1',
    ])));
  }

  /**
   * Codes are unique in a plan, not across plans; templates are unique too.
   */
  public function testUniqueness(): void {
    $plan = $this->loadFixture();
    $duplicate = $this->newRecord('ancora_teacher', ['plan' => $plan->id(), 'label' => 'Another', 'code' => 'TA']);
    $violations = $duplicate->validate();
    $this->assertCount(1, $violations);
    $this->assertSame('code', $violations[0]->getPropertyPath());
    $this->assertStringContainsString('TA', (string) $violations[0]->getMessage());

    // The same code in another plan is fine; plan codes are unique globally.
    $other = $this->newRecord('ancora_plan', ['label' => 'Other plan', 'code' => 'Q']);
    $this->assertSame([], $this->violationFields($other));
    $other->save();
    $same_code = $this->newRecord('ancora_teacher', ['plan' => $other->id(), 'label' => 'TA', 'code' => 'TA']);
    $this->assertSame([], $this->violationFields($same_code));
    $plan_again = $this->newRecord('ancora_plan', ['label' => 'Again', 'code' => 'P']);
    $this->assertSame(['code'], $this->violationFields($plan_again));

    // An existing record does not collide with itself.
    $this->assertSame([], $this->violationFields($this->byCode('ancora_teacher', 'TA', $plan)));

    $subject = $this->byCode('ancora_subject', 'A', $plan);
    $template = fn (int $sequence) => $this->newRecord('ancora_activity_template', [
      'plan' => $plan->id(),
      'subject' => $subject->id(),
      'period' => $this->byCode('ancora_period', 'PER', $plan)->id(),
      'sequence' => $sequence,
      'activity_type' => $this->byCode('ancora_activity_type', 'C1', $plan)->id(),
    ]);
    $this->assertSame(['sequence'], $this->violationFields($template(2)));
    $this->assertSame([], $this->violationFields($template(3)));

    $assignments = $this->container->get('entity_type.manager')->getStorage('ancora_teacher_assignment')->loadMultiple();
    $existing = reset($assignments);
    $this->assertInstanceOf(TeacherAssignment::class, $existing);
    $again = $this->newRecord('ancora_teacher_assignment', [
      'plan' => $plan->id(),
      'template' => $existing->get('template')->target_id,
      'teacher' => $existing->get('teacher')->target_id,
    ]);
    $this->assertSame(['teacher'], $this->violationFields($again));
  }

  /**
   * References stay within the plan and match specialty, level and type.
   */
  public function testRelationsAreValidated(): void {
    $plan = $this->loadFixture();
    $other = $this->newRecord('ancora_plan', ['label' => 'Other plan', 'code' => 'Q']);
    $other->save();
    $foreign_specialty = $this->newRecord('ancora_specialty', [
      'plan' => $other->id(),
      'label' => 'Foreign',
      'code' => 'E',
    ]);
    $foreign_specialty->save();

    // Subject B: the assignment checked below belongs to subject A.
    $subject = $this->byCode('ancora_subject', 'B', $plan);
    $subject->set('specialty', $foreign_specialty->id());
    $this->assertSame(['specialty'], $this->violationFields($subject));

    // A cohort of another specialty cannot join a group.
    $specialty_two = $this->newRecord('ancora_specialty', [
      'plan' => $plan->id(),
      'label' => 'Second programme',
      'code' => 'E2',
    ]);
    $specialty_two->save();
    $cohort = $this->newRecord('ancora_cohort', [
      'plan' => $plan->id(),
      'label' => 'Cohort 3',
      'code' => 'B3',
      'specialty' => $specialty_two->id(),
      'level' => $this->byCode('ancora_level', 'N', $plan)->id(),
    ]);
    $this->assertSame([], $this->violationFields($cohort));
    $cohort->save();
    $group = $this->byCode('ancora_student_group', 'G1', $plan);
    $group->get('cohorts')->appendItem($cohort->id());
    $this->assertSame(['cohorts'], $this->violationFields($group));

    // A teacher may only be given groups that attend the template.
    $lab = $this->newRecord('ancora_activity_type', ['plan' => $plan->id(), 'label' => 'Lab', 'code' => 'C2']);
    $lab->save();
    $lab_group = $this->newRecord('ancora_student_group', [
      'plan' => $plan->id(),
      'label' => 'Lab group',
      'code' => 'G3',
      'specialty' => $this->byCode('ancora_specialty', 'E', $plan)->id(),
      'level' => $this->byCode('ancora_level', 'N', $plan)->id(),
      'activity_type' => $lab->id(),
      'cohorts' => [$this->byCode('ancora_cohort', 'B1', $plan)->id()],
    ]);
    $this->assertSame([], $this->violationFields($lab_group));
    $lab_group->save();
    $assignments = $this->container->get('entity_type.manager')->getStorage('ancora_teacher_assignment')->loadMultiple();
    $assignment = reset($assignments);
    $this->assertInstanceOf(TeacherAssignment::class, $assignment);
    $this->assertSame([], $this->violationFields($assignment));
    $assignment->get('groups')->appendItem($lab_group->id());
    $this->assertSame(['groups'], $this->violationFields($assignment));
  }

  /**
   * A record still in use cannot be deleted, through any API.
   */
  public function testDeleteIsRefusedWhileReferenced(): void {
    $plan = $this->loadFixture();
    $guard = $this->container->get(ReferenceGuard::class);
    $dependents = $guard->dependents($plan);
    $this->assertSame(4, $dependents['ancora_activity_template']);
    $this->assertSame(8, $dependents['ancora_room_assignment']);
    $this->assertCount(12, $dependents);

    try {
      $plan->delete();
      $this->fail('A plan with records was deleted.');
    }
    catch (EntityStorageException $e) {
      $this->assertStringContainsString('cannot be deleted', $e->getMessage());
      $this->assertStringContainsString('4 activity templates', $e->getMessage());
    }
    $this->assertSame(1, $this->recordCount('ancora_plan'));

    $teacher = $this->byCode('ancora_teacher', 'TB', $plan);
    $this->assertSame(['ancora_teacher_assignment' => 2], $guard->dependents($teacher));
    $storage = $this->container->get('entity_type.manager')->getStorage('ancora_teacher_assignment');
    $storage->delete($storage->loadByProperties(['teacher' => $teacher->id()]));
    $this->assertSame([], $guard->dependents($teacher));
    $teacher->delete();
    $this->assertSame(1, $this->recordCount('ancora_teacher'));
  }

}
