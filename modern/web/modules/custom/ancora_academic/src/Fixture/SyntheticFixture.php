<?php

declare(strict_types=1);

namespace Drupal\ancora_academic\Fixture;

use Drupal\ancora_academic\AcademicEntityInterface;
use Drupal\ancora_academic\AcademicEntityTypes;
use Drupal\ancora_academic\Entity\Plan;
use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Session\AccountSwitcherInterface;

/**
 * Creates the synthetic dataset of dev/e2e/fixture.sql through the Entity API.
 *
 * Every name is invented; no historical data is involved. The dataset is:
 * plan P; period PER of 5 days x 4 slots from 8:00, slots of one hour, on day
 * 1 of the plan; activity type C1 (one slot); specialty E and level N;
 * cohorts B1 and B2 (20 students) in lecture groups G1 and G2; subjects A and
 * B with two weekly templates each (A1, A2, B1, B2); teacher TA for A1 and A2
 * (groups G1 and G2); rooms L1 (priority 1) and L2 (priority 2), capacity
 * 50, for every template.
 *
 * As in the legacy end-to-end harness, the fixture itself has no teacher for
 * subject B: dev/e2e/lib.sh adds "Teacher Beta" over HTTP and assigns it to
 * B1 and B2 for both groups. With $complete = TRUE (the default) those data
 * entry steps are applied too, which gives the dataset the engine needs to
 * place 8 of 8 activities.
 *
 * Every record is validated before it is saved, so the fixture also proves
 * the constraints accept a coherent dataset. The records belong to one user,
 * as the fixture rows belong to "admin-superuser": the plan and the
 * catalogues are that user's, and they are created and validated as that
 * user (the reference choices are the user's own records). Loading is
 * refused when that user already has a plan P, so a run never mixes with
 * earlier data.
 */
final class SyntheticFixture {

  /**
   * The code of the fixture plan.
   */
  public const PLAN_CODE = 'P';

  /**
   * The user ID of the owner of the records being created.
   */
  private int $owner = 0;

  public function __construct(
    private readonly EntityTypeManagerInterface $entityTypeManager,
    private readonly Connection $database,
    private readonly AccountSwitcherInterface $accountSwitcher,
  ) {}

  /**
   * Creates the dataset in one transaction and returns its plan.
   *
   * @param bool $complete
   *   Also apply the data entry steps of the HTTP harness (Teacher Beta).
   * @param \Drupal\Core\Session\AccountInterface|null $owner
   *   The user who owns the records; by default the site's first account.
   *
   * @throws \RuntimeException
   *   When the owner has plan P or a record does not validate; nothing is
   *   kept.
   */
  public function load(bool $complete = TRUE, ?AccountInterface $owner = NULL): Plan {
    $owner ??= $this->entityTypeManager->getStorage('user')->load(1);
    if (!$owner instanceof AccountInterface) {
      throw new \RuntimeException('The fixture needs an owner account.');
    }
    $transaction = $this->database->startTransaction();
    $this->accountSwitcher->switchTo($owner);
    try {
      return $this->doLoad($complete, (int) $owner->id());
    }
    catch (\Throwable $e) {
      $transaction->rollBack();
      throw $e;
    }
    finally {
      $this->accountSwitcher->switchBack();
    }
  }

  /**
   * Creates the dataset.
   */
  private function doLoad(bool $complete, int $owner): Plan {
    $this->owner = $owner;
    $existing = $this->entityTypeManager->getStorage('ancora_plan')->loadByProperties([
      'code' => self::PLAN_CODE,
      'uid' => $owner,
    ]);
    if ($existing) {
      throw new \RuntimeException(sprintf('The user already has a plan with code "%s"; the fixture is loaded only once.', self::PLAN_CODE));
    }

    $plan = $this->create('ancora_plan', [
      'label' => 'Plan',
      'code' => self::PLAN_CODE,
      'duration_days' => 30,
      'active' => TRUE,
    ]);
    assert($plan instanceof Plan);
    $in = ['plan' => $plan->id()];

    $period = $this->create('ancora_period', $in + [
      'label' => 'Week',
      'code' => 'PER',
      'days' => 5,
      'slots_per_day' => 4,
      'start_hour' => 8,
      'slot_duration' => 1,
      'plan_days' => [1],
    ]);
    $lecture = $this->create('ancora_activity_type', $in + [
      'label' => 'Lecture',
      'code' => 'C1',
      'duration_slots' => 1,
      'priority' => 1,
      'cost' => 1,
    ]);
    $specialty = $this->create('ancora_specialty', $in + ['label' => 'Program', 'code' => 'E']);
    $level = $this->create('ancora_level', $in + ['label' => 'Year 1', 'code' => 'N']);
    $of = ['specialty' => $specialty->id(), 'level' => $level->id()];

    $groups = [];
    foreach ([1, 2] as $n) {
      $cohort = $this->create('ancora_cohort', $in + $of + ['label' => "Cohort $n", 'code' => "B$n", 'enrollment' => 20]);
      $groups[] = $this->create('ancora_student_group', $in + $of + [
        'label' => "Group $n",
        'code' => "G$n",
        'activity_type' => $lecture->id(),
        'cohorts' => [$cohort->id()],
      ]);
    }
    $group_ids = array_map(static fn (AcademicEntityInterface $g) => $g->id(), $groups);

    $templates = [];
    foreach (['A' => 'Subject Alpha', 'B' => 'Subject Beta'] as $code => $label) {
      $subject = $this->create('ancora_subject', $in + $of + ['label' => $label, 'code' => $code]);
      foreach ([1, 2] as $sequence) {
        $templates[$code . $sequence] = $this->create('ancora_activity_template', $in + [
          'subject' => $subject->id(),
          'period' => $period->id(),
          'sequence' => $sequence,
          'activity_type' => $lecture->id(),
        ]);
      }
    }

    $teacher_alpha = $this->create('ancora_teacher', $in + ['label' => 'Teacher Alpha', 'code' => 'TA']);
    foreach (['A1', 'A2'] as $template) {
      $this->assign($in, $templates[$template]->id(), $teacher_alpha->id(), $group_ids);
    }

    $rooms = [];
    foreach ([1, 2] as $n) {
      $rooms[$n] = $this->create('ancora_room', $in + ['label' => "Room $n", 'code' => "L$n", 'capacity' => 50]);
    }
    foreach ($templates as $template) {
      foreach ($rooms as $priority => $room) {
        $this->create('ancora_room_assignment', $in + [
          'template' => $template->id(),
          'room' => $room->id(),
          'priority' => $priority,
        ]);
      }
    }

    if ($complete) {
      // dev/e2e/lib.sh: add_Profesor, add_ProfesorParaActividad for B1 and
      // B2, custom_ActualizaProfesorParaGrupo for G1 and G2.
      $teacher_beta = $this->create('ancora_teacher', $in + ['label' => 'Teacher Beta', 'code' => 'TB']);
      foreach (['B1', 'B2'] as $template) {
        $this->assign($in, $templates[$template]->id(), $teacher_beta->id(), $group_ids);
      }
    }

    return $plan;
  }

  /**
   * Assigns a teacher to a template for the given groups, with priority 1.
   *
   * @param array<string, mixed> $in
   *   The plan value.
   * @param int|string|null $template
   *   The template ID.
   * @param int|string|null $teacher
   *   The teacher ID.
   * @param list<int|string|null> $groups
   *   The group IDs.
   */
  private function assign(array $in, int|string|null $template, int|string|null $teacher, array $groups): void {
    $this->create('ancora_teacher_assignment', $in + [
      'template' => $template,
      'teacher' => $teacher,
      'priority' => 1,
      'groups' => $groups,
    ]);
  }

  /**
   * Creates, validates and saves a record.
   *
   * @param string $entity_type_id
   *   The entity type.
   * @param array<string, mixed> $values
   *   The field values.
   */
  private function create(string $entity_type_id, array $values): AcademicEntityInterface {
    if (AcademicEntityTypes::isOwned($entity_type_id)) {
      // A catalogue record serves every plan of its owner: no plan.
      unset($values['plan']);
      $values['uid'] = $this->owner;
    }
    $entity = $this->entityTypeManager->getStorage($entity_type_id)->create($values);
    assert($entity instanceof AcademicEntityInterface);
    $violations = $entity->validate();
    if ($violations->count() > 0) {
      $messages = [];
      foreach ($violations as $violation) {
        $messages[] = $violation->getPropertyPath() . ': ' . strip_tags((string) $violation->getMessage());
      }
      throw new \RuntimeException(sprintf('Invalid %s: %s', $entity_type_id, implode('; ', $messages)));
    }
    $entity->save();
    return $entity;
  }

}
