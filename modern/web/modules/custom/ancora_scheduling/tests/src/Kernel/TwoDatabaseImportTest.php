<?php

declare(strict_types=1);

namespace Drupal\Tests\ancora_scheduling\Kernel;

use Drupal\ancora_engine\Job\Job;
use Drupal\ancora_engine\Job\JobOptions;
use Drupal\ancora_engine\Job\JobState;
use Drupal\ancora_engine\JobRepository;
use Drupal\ancora_scheduling\Migration\TwoDatabaseImport;
use Drupal\ancora_scheduling\ScopeMapper;
use Drupal\ancora_scheduling\Sync\SiteRows;
use Drupal\Tests\ancora_academic\Kernel\AcademicKernelTestBase;
use PHPUnit\Framework\Attributes\Group;

/**
 * Import of a two-database site's placements as placement entities (#96).
 *
 * The engine database is not available here: the rows are built from the
 * site rows, as TwoDatabaseImport::read() returns them. The migration end
 * to end (engine database, backups, switch, continuation, rollback) is
 * exercised by scripts/tenant-verify.sh.
 */
#[Group('ancora_scheduling')]
final class TwoDatabaseImportTest extends AcademicKernelTestBase {

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
    $this->installSchema('ancora_engine', ['ancora_engine_job']);
    $this->installEntitySchema('ancora_placement');
  }

  /**
   * Jobs of the fixture's scope, finished in the given states.
   *
   * @return array{0: list<int>, 1: array<string, array<string, string>>}
   *   The job ids, and per legacy table code => engine id.
   */
  private function jobs(array $states): array {
    $plan = $this->loadFixture();
    $records = [
      $plan,
      $this->byCode('ancora_period', 'PER', $plan),
      $this->byCode('ancora_specialty', 'E'),
      $this->byCode('ancora_level', 'N'),
    ];
    $repository = $this->container->get(JobRepository::class);
    $ids = [];
    foreach ($states as $state) {
      $repository->enqueue(ScopeMapper::scope(...$records), new JobOptions(), 60, (int) $this->owner->id(), NULL, ScopeMapper::source(...$records));
      $job = $repository->claim('test');
      $this->assertInstanceOf(Job::class, $job);
      $repository->finish($job, $state, $state === JobState::Cancelled ? ['kept' => 'partial'] : []);
      $ids[] = $job->id;
    }
    [$rows] = $this->container->get(SiteRows::class)->site();
    $engine = [];
    foreach (['plantilla_de_actividad', 'grupo', 'profesor', 'lugar'] as $table) {
      foreach ($rows[$table] as $row) {
        $engine[$table][substr((string) $row['id'], 11)] = (string) $row['id'];
      }
    }
    return [$ids, $engine];
  }

  /**
   * A row of the engine database's "actividad".
   */
  private function row(array $ids, string $engine_id, string $template, string $group, int $day, int $slot): array {
    return [
      'id' => $engine_id,
      'plantilla' => $ids['plantilla_de_actividad'][$template],
      'grupo' => $ids['grupo'][$group],
      'profesor' => $ids['profesor']['TA'],
      'lugar' => $ids['lugar']['L1'],
      'dia' => (string) $day,
      'turno' => (string) $slot,
      'momento_de_colocacion' => '8463176.47',
      'creador' => ScopeMapper::creatorOf((int) $this->owner->id()),
    ];
  }

  /**
   * What read() returns.
   */
  private function read(array $rows, int $resources = 0): array {
    return ['rows' => $rows, 'resources' => $resources, 'cancel_requests' => 0];
  }

  /**
   * The templates' codes, sorted.
   */
  private function templates(array $ids): array {
    $templates = array_keys($ids['plantilla_de_actividad']);
    sort($templates);
    return $templates;
  }

  /**
   * Imported in row order with their engine ids, linked to the job shown.
   */
  public function testImportKeepsIdentityOrderAndJob(): void {
    // The latest job that changed the scope's placements is the cancelled
    // one (a draft): the failed run after it changed nothing.
    [[, $cancelled], $ids] = $this->jobs([JobState::Succeeded, JobState::Cancelled, JobState::Failed]);
    $t = $this->templates($ids);
    $rows = [
      $this->row($ids, 'zz-2', $t[0], 'G2', 1, 4),
      $this->row($ids, 'aa-1', $t[0], 'G1', 1, 1),
      $this->row($ids, 'mm-3', $t[1], 'G1', 2, 1),
    ];
    $import = $this->container->get(TwoDatabaseImport::class);
    $plan = $import->plan($this->read($rows));
    $this->assertSame([], $plan['problems']);
    $this->assertSame(['imported' => 3, 'kept' => 0], $import->import($plan));

    $storage = $this->container->get('entity_type.manager')->getStorage('ancora_placement');
    $placements = $storage->loadMultiple($storage->getQuery()->accessCheck(FALSE)->sort('id')->execute());
    $this->assertSame(['zz-2', 'aa-1', 'mm-3'], array_values(array_map(fn ($p) => $p->get('engine_id')->value, $placements)), 'entity id order = row order');
    $first = reset($placements);
    $values = [
      $first->get('job')->value,
      $first->get('day')->value,
      $first->get('slot')->value,
      (float) $first->get('placed_at')->value,
    ];
    $this->assertSame([(string) $cancelled, '1', '4', 8463176.47], $values);
    $this->assertSame((int) $this->owner->id(), $first->getCreatorId());

    // Running it again is the recovery of an interrupted migration.
    $this->assertSame(['imported' => 0, 'kept' => 3], $import->import($import->plan($this->read($rows))));
    $this->assertTrue($first->get('other_resources')->isEmpty());
    $this->assertSame(3, $this->recordCount('ancora_placement'));
    $this->assertSame(0, $import->foreign($this->read($rows)));
    $this->assertSame(1, $import->foreign($this->read(array_slice($rows, 1))));
  }

  /**
   * The otros_recursos_usados rows of a row become its other resources (D9d).
   */
  public function testResourceUsesAreImported(): void {
    [, $ids] = $this->jobs([JobState::Succeeded]);
    $t = $this->templates($ids);
    $resource = $this->newRecord('ancora_other_resource', ['label' => 'Projector', 'code' => 'R1']);
    $resource->save();
    $row = $this->row($ids, 'r-1', $t[0], 'G1', 1, 1) + ['otros_recursos' => [sprintf('%010d:R1', $resource->id())]];
    $import = $this->container->get(TwoDatabaseImport::class);
    $plan = $import->plan($this->read([$row], 1));
    $this->assertSame([], $plan['problems']);
    $this->assertSame(['imported' => 1, 'kept' => 0], $import->import($plan));
    $placement = current($this->container->get('entity_type.manager')->getStorage('ancora_placement')->loadByProperties(['engine_id' => 'r-1']));
    $this->assertSame((string) $resource->id(), (string) $placement->get('other_resources')->target_id);

    $gone = $this->row($ids, 'r-2', $t[0], 'G2', 1, 2) + ['otros_recursos' => ['9999999999:GONE']];
    $this->assertStringContainsString('an other resource does not exist', $import->plan($this->read([$gone], 1))['problems'][0]);
  }

  /**
   * Every problem is reported, and nothing is imported.
   */
  public function testProblemsAreRefused(): void {
    [, $ids] = $this->jobs([JobState::Failed]);
    $t = $this->templates($ids);
    $import = $this->container->get(TwoDatabaseImport::class);

    $plan = $import->plan($this->read([$this->row($ids, 'a', $t[0], 'G1', 1, 1)], 2));
    $this->assertCount(1, $plan['problems']);
    $this->assertStringContainsString('no job of its scope', $plan['problems'][0]);

    $unmapped = $this->row($ids, 'b', $t[0], 'G1', 1, 1);
    $unmapped['plantilla'] = '9999999999:GONE';
    $outside = $this->row($ids, 'c', $t[0], 'G2', 99, 1);
    $plan = $import->plan($this->read([$unmapped, $outside]));
    $this->assertStringContainsString('no longer exists', $plan['problems'][0]);
    $this->assertStringContainsString('outside the period', $plan['problems'][1]);

    $this->expectException(\RuntimeException::class);
    try {
      $import->import($plan);
    }
    finally {
      $this->assertSame(0, $this->recordCount('ancora_placement'));
    }
  }

  /**
   * A failure halfway keeps nothing; the same template twice is refused.
   */
  public function testFailureHalfwayAndDuplicates(): void {
    [, $ids] = $this->jobs([JobState::Succeeded]);
    $t = $this->templates($ids);
    $import = $this->container->get(TwoDatabaseImport::class);
    $rows = [$this->row($ids, 'a', $t[0], 'G1', 1, 1), $this->row($ids, 'b', $t[0], 'G2', 1, 2)];
    try {
      $import->import($import->plan($this->read($rows)), 1);
      $this->fail('the test hook did not stop the import');
    }
    catch (\RuntimeException $e) {
      $this->assertStringContainsString('test hook', $e->getMessage());
    }
    $this->assertSame(0, $this->recordCount('ancora_placement'));

    $twice = [$this->row($ids, 'a', $t[0], 'G1', 1, 1), $this->row($ids, 'b', $t[0], 'G1', 2, 1)];
    $this->assertStringContainsString('placed twice', $import->plan($this->read($twice))['problems'][0]);
  }

}
