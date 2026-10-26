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

    // Catalogue records have an owner and no plan; plan records a plan.
    $this->assertFalse($teacher->hasField('plan'));
    $this->assertNull($teacher->getPlanId());
    $this->assertSame((int) $this->owner->id(), $teacher->getCreatorId());
    $this->assertSame((int) $this->owner->id(), $plan->getCreatorId());
    $this->assertSame((int) $this->owner->id(), $period->getCreatorId());
  }

  /**
   * One user's catalogues serve every plan of that user (legacy PAR-003).
   */
  public function testCataloguesAreSharedAcrossTheCreatorsPlans(): void {
    $plan = $this->loadFixture();
    $second = $this->newRecord('ancora_plan', ['label' => 'Second plan', 'code' => 'Q']);
    $this->assertSame([], $this->violationFields($second));
    $second->save();
    $period = $this->newRecord('ancora_period', [
      'plan' => $second->id(),
      'label' => 'Week',
      'code' => 'PER',
      'days' => 5,
      'slots_per_day' => 4,
      'start_hour' => 8,
      'slot_duration' => 1,
    ]);
    $this->assertSame([], $this->violationFields($period));
    $period->save();
    $group = $this->newRecord('ancora_student_group', [
      'plan' => $second->id(),
      'label' => 'Group 1',
      'code' => 'G1',
      'activity_type' => $this->byCode('ancora_activity_type', 'C1')->id(),
      'specialty' => $this->byCode('ancora_specialty', 'E')->id(),
      'level' => $this->byCode('ancora_level', 'N')->id(),
      'cohorts' => [$this->byCode('ancora_cohort', 'B1')->id()],
    ]);
    $this->assertSame([], $this->violationFields($group));
    $group->save();
    $template = $this->newRecord('ancora_activity_template', [
      'plan' => $second->id(),
      'subject' => $this->byCode('ancora_subject', 'A')->id(),
      'period' => $period->id(),
      'sequence' => 1,
      'activity_type' => $this->byCode('ancora_activity_type', 'C1')->id(),
    ]);
    $this->assertSame([], $this->violationFields($template));
    $template->save();
    $assignment = $this->newRecord('ancora_teacher_assignment', [
      'plan' => $second->id(),
      'template' => $template->id(),
      'teacher' => $this->byCode('ancora_teacher', 'TA')->id(),
      'groups' => [$group->id()],
    ]);
    $this->assertSame([], $this->violationFields($assignment));
    $assignment->save();
    // The template of the first plan cannot be used in the second plan.
    $first_templates = $this->container->get('entity_type.manager')->getStorage('ancora_activity_template')->loadByProperties(['plan' => $plan->id()]);
    $foreign = $this->newRecord('ancora_room_assignment', [
      'plan' => $second->id(),
      'template' => reset($first_templates)->id(),
      'room' => $this->byCode('ancora_room', 'L1')->id(),
    ]);
    $this->assertContains('template', $this->violationFields($foreign));
    // The teacher is used by assignments of both plans.
    $this->assertSame(['ancora_teacher_assignment' => 3], $this->container->get(ReferenceGuard::class)->dependents($this->byCode('ancora_teacher', 'TA')));
  }

  /**
   * Required fields and numeric ranges are enforced.
   */
  public function testRequiredFieldsAndRanges(): void {
    $plan = $this->loadFixture();
    $this->assertSame(['code', 'label'], $this->violationFields($this->newRecord('ancora_teacher', [])));
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
      'label' => 'Lab',
      'code' => 'C2',
    ]);
    $this->assertSame([], $this->violationFields($type([])));
    $this->assertSame(['duration_slots'], $this->violationFields($type(['duration_slots' => 0])));
    $this->assertSame(['priority'], $this->violationFields($type(['priority' => 0])));
    $this->assertSame(['cost'], $this->violationFields($type(['cost' => -1])));

    $this->assertSame(['capacity'], $this->violationFields($this->newRecord('ancora_room', [
      'label' => 'Broken room',
      'code' => 'L9',
      'capacity' => -1,
    ])));
    $this->assertSame(['code'], $this->violationFields($this->newRecord('ancora_teacher', [
      'label' => 'Spaced code',
      'code' => 'T 1',
    ])));
  }

  /**
   * Codes are unique per creator (catalogues, plans) or plan, not per site.
   *
   * Templates and assignments are unique too.
   */
  public function testUniqueness(): void {
    $plan = $this->loadFixture();
    $duplicate = $this->newRecord('ancora_teacher', ['label' => 'Another', 'code' => 'TA']);
    $violations = $duplicate->validate();
    $this->assertCount(1, $violations);
    $this->assertSame('code', $violations[0]->getPropertyPath());
    $this->assertStringContainsString('TA', (string) $violations[0]->getMessage());
    $plan_again = $this->newRecord('ancora_plan', ['label' => 'Again', 'code' => 'P']);
    $this->assertSame(['code'], $this->violationFields($plan_again));

    // Another user may use the same codes, for plans too (two legacy users
    // each had their own rows).
    $other = $this->createUser(['view academic data', 'edit academic data']);
    $this->setCurrentUser($other);
    $other_plan = $this->newRecord('ancora_plan', ['label' => 'Plan', 'code' => 'P']);
    $this->assertSame([], $this->violationFields($other_plan));
    $same_code = $this->newRecord('ancora_teacher', ['label' => 'Teacher Alpha', 'code' => 'TA']);
    $this->assertSame([], $this->violationFields($same_code));
    $this->setCurrentUser($this->owner);

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
   * References name the creator's records and match specialty, level, type.
   */
  public function testRelationsAreValidated(): void {
    $plan = $this->loadFixture();
    $other = $this->createUser(['view academic data', 'edit academic data']);
    $foreign_specialty = $this->newRecord('ancora_specialty', [
      'uid' => $other->id(),
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
      'label' => 'Second programme',
      'code' => 'E2',
    ]);
    $specialty_two->save();
    $cohort = $this->newRecord('ancora_cohort', [
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
    $lab = $this->newRecord('ancora_activity_type', ['label' => 'Lab', 'code' => 'C2']);
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
    // Only the plan's own records reference it; catalogues have no plan.
    $this->assertSame([
      'ancora_period',
      'ancora_student_group',
      'ancora_activity_template',
      'ancora_teacher_assignment',
      'ancora_room_assignment',
    ], array_keys($dependents));

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
