<?php

declare(strict_types=1);

namespace Drupal\Tests\ancora_scheduling\Kernel;

use Drupal\ancora_academic\Entity\Plan;
use Drupal\ancora_academic\Entity\StudentGroup;
use Drupal\ancora_academic\GroupMembership;
use Drupal\ancora_academic\Hook\MembershipHooks;
use Drupal\ancora_academic\Plugin\Validation\Constraint\GroupMembershipConstraintValidator;
use Drupal\ancora_engine\EngineSettings;
use Drupal\ancora_scheduling\Hook\GroupActivities;
use Drupal\Core\Site\Settings;
use Drupal\Tests\ancora_academic\Kernel\AcademicKernelTestBase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;

/**
 * Cohort membership with the legacy move semantics and guard (#70, D10).
 *
 * Legacy mueve_o_crea_brigada_de_grupo() (cases 113, 136): a cohort is in
 * one group per plan, activity type, specialty and level, and adding it to
 * another group of that slot moves it; the move is refused when the target
 * group or the group it leaves has placed activities; a removal is never
 * refused. The placements are the workspace mode's placement entities.
 */
#[CoversClass(GroupMembership::class)]
#[CoversClass(MembershipHooks::class)]
#[CoversClass(GroupMembershipConstraintValidator::class)]
#[CoversClass(GroupActivities::class)]
#[Group('ancora_scheduling')]
final class GroupMembershipTest extends AcademicKernelTestBase {

  /**
   * The fixture plan.
   */
  private Plan $plan;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'ancora_academic',
    'ancora_engine',
    'ancora_scheduling',
    'datetime',
    'system',
    'user',
    'views',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    // Workspace mode: the placements are entities of the site.
    $this->container->set('ancora_engine.settings', new EngineSettings(new Settings(['ancora_engine' => ['mode' => 'workspace']])));
    $this->installSchema('ancora_engine', ['ancora_engine_job']);
    $this->installEntitySchema('ancora_placement');
  }

  /**
   * The cohort codes of every group, "G1=B1 G2=B2".
   */
  private function memberships(): string {
    $storage = $this->container->get('entity_type.manager')->getStorage('ancora_student_group');
    $storage->resetCache();
    $out = [];
    foreach ($storage->loadMultiple() as $group) {
      assert($group instanceof StudentGroup);
      $codes = array_map(static fn ($c): string => $c->getCode(), $group->get('cohorts')->referencedEntities());
      sort($codes);
      $out[] = $group->getCode() . '=' . implode(',', $codes);
    }
    sort($out);
    return implode(' ', $out);
  }

  /**
   * A group of the fixture, freshly loaded.
   */
  private function group(string $code): StudentGroup {
    $storage = $this->container->get('entity_type.manager')->getStorage('ancora_student_group');
    $storage->resetCache();
    $group = $this->byCode('ancora_student_group', $code, $this->plan);
    assert($group instanceof StudentGroup);
    return $group;
  }

  /**
   * Places template A1 for a group (a placement entity).
   */
  private function place(StudentGroup $group): void {
    $entity_type_manager = $this->container->get('entity_type.manager');
    $templates = $entity_type_manager->getStorage('ancora_activity_template')->loadByProperties(['sequence' => 1]);
    $entity_type_manager->getStorage('ancora_placement')->create([
      'plan' => $this->plan->id(),
      'period' => $this->byCode('ancora_period', 'PER', $this->plan)->id(),
      'template' => array_key_first($templates),
      'student_group' => $group->id(),
      'teacher' => $this->byCode('ancora_teacher', 'TA')->id(),
      'room' => $this->byCode('ancora_room', 'L1')->id(),
      'day' => 1,
      'slot' => 1,
      'engine_id' => 'test:' . $group->id(),
      'job' => 1,
    ])->save();
  }

  /**
   * Adding a cohort to another group of its slot moves it; other slots add.
   */
  public function testMoveSemantics(): void {
    $this->plan = $this->loadFixture();
    $this->assertSame('G1=B1 G2=B2', $this->memberships());

    $g2 = $this->group('G2');
    $g2->get('cohorts')->appendItem($this->byCode('ancora_cohort', 'B1')->id());
    $this->assertSame([], $this->violationFields($g2));
    $g2->save();
    $this->assertSame('G1= G2=B1,B2', $this->memberships(), 'B1 moved from G1 to G2');

    // A new group of the same slot takes the cohort too.
    $g3 = $this->newRecord('ancora_student_group', [
      'plan' => $this->plan->id(),
      'label' => 'Group 3',
      'code' => 'G3',
      'specialty' => $this->byCode('ancora_specialty', 'E')->id(),
      'level' => $this->byCode('ancora_level', 'N')->id(),
      'activity_type' => $this->byCode('ancora_activity_type', 'C1')->id(),
      'cohorts' => [$this->byCode('ancora_cohort', 'B2')->id()],
    ]);
    $this->assertSame([], $this->violationFields($g3));
    $g3->save();
    $this->assertSame('G1= G2=B1 G3=B2', $this->memberships());

    // Another activity type is another slot: the cohort is added.
    $lab = $this->newRecord('ancora_activity_type', [
      'label' => 'Lab',
      'code' => 'C2',
      'duration_slots' => 1,
      'priority' => 2,
      'cost' => 1,
    ]);
    $lab->save();
    $g4 = $this->newRecord('ancora_student_group', [
      'plan' => $this->plan->id(),
      'label' => 'Lab group',
      'code' => 'G4',
      'specialty' => $this->byCode('ancora_specialty', 'E')->id(),
      'level' => $this->byCode('ancora_level', 'N')->id(),
      'activity_type' => $lab->id(),
      'cohorts' => [$this->byCode('ancora_cohort', 'B1')->id()],
    ]);
    $g4->save();
    // So is another plan of the same owner.
    $plan2 = $this->newRecord('ancora_plan', ['label' => 'Plan 2', 'code' => 'P2', 'duration_days' => 30]);
    $plan2->save();
    $g5 = $this->newRecord('ancora_student_group', [
      'plan' => $plan2->id(),
      'label' => 'Group of plan 2',
      'code' => 'G5',
      'specialty' => $this->byCode('ancora_specialty', 'E')->id(),
      'level' => $this->byCode('ancora_level', 'N')->id(),
      'activity_type' => $this->byCode('ancora_activity_type', 'C1')->id(),
      'cohorts' => [$this->byCode('ancora_cohort', 'B1')->id()],
    ]);
    $g5->save();
    $this->assertSame('G1= G2=B1 G3=B2 G4=B1 G5=B1', $this->memberships());
  }

  /**
   * The guard: no cohort joins, or leaves for another group, a placed group.
   */
  public function testActivityGuard(): void {
    $this->plan = $this->loadFixture();
    $membership = $this->container->get(GroupMembership::class);
    $g1 = $this->group('G1');
    $this->assertFalse($membership->hasActivities($g1));
    $this->place($g1);
    $this->assertTrue($membership->hasActivities($g1));
    $this->assertFalse($membership->hasActivities($this->group('G2')));

    // The target group has activities.
    $g1->get('cohorts')->appendItem($this->byCode('ancora_cohort', 'B2')->id());
    $violations = $g1->validate();
    $this->assertCount(1, $violations);
    $this->assertSame('cohorts', $violations[0]->getPropertyPath());
    $this->assertStringContainsString('the group already has placed activities', strip_tags((string) $violations[0]->getMessage()));

    // The group the cohort would leave has activities.
    $g2 = $this->group('G2');
    $g2->get('cohorts')->appendItem($this->byCode('ancora_cohort', 'B1')->id());
    $violations = $g2->validate();
    $this->assertCount(1, $violations);
    $this->assertStringContainsString('it would leave the group Group 1', strip_tags((string) $violations[0]->getMessage()));

    // Removing a cohort from a placed group is not refused.
    $g1 = $this->group('G1');
    $g1->set('cohorts', []);
    $this->assertSame([], $this->violationFields($g1));
    $g1->save();
    $this->assertSame('G1= G2=B2', $this->memberships());
    // Other edits of a placed group are not refused either.
    $g1 = $this->group('G1');
    $g1->set('label', 'Renamed group');
    $this->assertSame([], $this->violationFields($g1));
  }

  /**
   * Two-database mode without an engine database: nothing is placed.
   */
  public function testTwoDatabaseModeWithoutEngine(): void {
    $this->plan = $this->loadFixture();
    $g1 = $this->group('G1');
    $this->place($g1);
    $hook = new GroupActivities(
      $this->container->get('entity_type.manager'),
      new EngineSettings(new Settings([])),
      $this->container->get('ancora_engine.engine_client'),
      $this->container->get('logger.channel.ancora_scheduling'),
    );
    // The placement entity is not read in this mode; no engine database is
    // configured, so no run could have placed anything there.
    $this->assertFalse($hook->hasActivities($g1));
  }

}
