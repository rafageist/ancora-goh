<?php

declare(strict_types=1);

namespace Drupal\Tests\ancora_scheduling\Kernel;

use Drupal\ancora_academic\Entity\Plan;
use Drupal\ancora_academic\Entity\StudentGroup;
use Drupal\ancora_academic\GroupMembership;
use Drupal\ancora_academic\Plugin\Validation\Constraint\GroupMembershipConstraintValidator;
use Drupal\ancora_engine\Engine\EngineClient;
use Drupal\ancora_engine\EngineSettings;
use Drupal\ancora_scheduling\Hook\GroupActivities;
use Drupal\Core\Site\Settings;
use Drupal\Tests\ancora_academic\Kernel\AcademicKernelTestBase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;

/**
 * The cohort guard fails closed when the engine cannot be read (#70, D10).
 *
 * Legacy mueve_o_crea_brigada_de_grupo() (cases 113, 136): a cohort is in
 * one group per plan, activity type, specialty and level, and adding it to
 * another group of that slot moves it; the move is refused when the target
 * group or the group it leaves has placed activities; a removal is never
 * refused. The placements are the workspace mode's placement entities.
 */
#[CoversClass(GroupMembership::class)]
#[CoversClass(GroupMembershipConstraintValidator::class)]
#[CoversClass(GroupActivities::class)]
#[Group('ancora_scheduling')]
final class GroupGuardUnreachableTest extends AcademicKernelTestBase {

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
    // Two-database mode, with a configured engine database that cannot be
    // reached.
    $settings = new EngineSettings(new Settings([
      'ancora_engine' => [
        'mode' => 'two_database',
        'database' => [
          'host' => '/nonexistent-ancora-socket-dir',
          'port' => 1,
          'database' => 'unreachable',
          'username' => 'unreachable',
        ],
      ],
    ]));
    $this->container->set('ancora_engine.settings', $settings);
    $this->container->set('ancora_engine.engine_client', new EngineClient($settings));
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
    $this->container->get('entity_type.manager')->getStorage('ancora_student_group')->resetCache();
    $group = $this->byCode('ancora_student_group', $code, $this->plan);
    assert($group instanceof StudentGroup);
    return $group;
  }

  /**
   * Two-database mode with an unreachable engine: the guard fails closed.
   */
  public function testUnreachableEngineRefusesTheChange(): void {
    $this->plan = $this->loadFixture();
    $membership = $this->container->get(GroupMembership::class);
    $this->assertNull($membership->hasActivities($this->group('G1')));

    // Adding a cohort (a move out of G1) is refused with the reason.
    $g2 = $this->group('G2');
    $g2->get('cohorts')->appendItem($this->byCode('ancora_cohort', 'B1')->id());
    $violations = $g2->validate();
    $this->assertGreaterThan(0, $violations->count());
    $this->assertStringContainsString('cannot be verified right now', strip_tags((string) $violations[0]->getMessage()));
    // Removing a cohort is not a move and needs no check.
    $g1 = $this->group('G1');
    $g1->set('cohorts', []);
    $this->assertSame([], $this->violationFields($g1));
    $this->assertSame('G1=B1 G2=B2', $this->memberships());
  }

}
