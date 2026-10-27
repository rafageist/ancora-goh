<?php

declare(strict_types=1);

namespace Drupal\Tests\ancora_scheduling\Kernel;

use Drupal\ancora_engine\Job\JobState;
use Drupal\ancora_engine\Engine\PublicationException;
use Drupal\ancora_engine\Engine\RunResult;
use Drupal\ancora_engine\Job\Job;
use Drupal\ancora_engine\Job\JobOptions;
use Drupal\ancora_engine\JobRepository;
use Drupal\ancora_scheduling\ScopeMapper;
use Drupal\ancora_scheduling\Sync\SiteRows;
use Drupal\ancora_scheduling\Sync\WorkspaceSync;
use Drupal\ancora_scheduling\Timetable\PlacementPublisher;
use Drupal\Core\Cache\Cache;
use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Tests\ancora_academic\Kernel\AcademicKernelTestBase;
use PHPUnit\Framework\Attributes\Group;

/**
 * Publication of a workspace run as placement entities (#95).
 *
 * The workspace run itself is exercised by scripts/workspace-journey.sh and
 * scripts/parity-diff.sh --workspace; here the output rows are built from
 * the site rows, as the engine would return them.
 */
#[Group('ancora_scheduling')]
final class PlacementPublisherTest extends AcademicKernelTestBase {

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
  public function register(ContainerBuilder $container): void {
    parent::register($container);
    // Kernel tests keep every cache bin in memory. The cold-cache test needs
    // the database backend of the sites for the bins the publication writes.
    if ($this->name() === 'testPublishWithColdCaches') {
      foreach (['cache.config', 'cache.access_policy', 'cache.entity', 'cache.default', 'cache.discovery', 'cache.data'] as $id) {
        if ($container->hasDefinition($id)) {
          $definition = $container->getDefinition($id);
          $tags = $definition->getTags();
          $tags['cache.bin'][0]['default_backend'] = 'cache.backend.database';
          $definition->setTags($tags);
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->installSchema('ancora_engine', ['ancora_engine_job']);
    $this->installEntitySchema('ancora_placement');
  }

  /**
   * A running job of the fixture's scope, and engine ids by code.
   *
   * @return array{0: \Drupal\ancora_engine\Job\Job, 1: array<string, array<string, string>>}
   *   The claimed job and, per legacy table, code => engine id.
   */
  private function runningJob(): array {
    $plan = $this->loadFixture();
    $records = [
      $plan,
      $this->byCode('ancora_period', 'PER', $plan),
      $this->byCode('ancora_specialty', 'E'),
      $this->byCode('ancora_level', 'N'),
    ];
    $jobs = $this->container->get(JobRepository::class);
    $jobs->enqueue(ScopeMapper::scope(...$records), new JobOptions(), 60, (int) $this->owner->id(), NULL, ScopeMapper::source(...$records));
    $job = $jobs->claim('test');
    $this->assertInstanceOf(Job::class, $job);
    [$rows] = $this->container->get(SiteRows::class)->site();
    $ids = [];
    foreach (['plantilla_de_actividad', 'grupo', 'profesor', 'lugar'] as $table) {
      foreach ($rows[$table] as $row) {
        $ids[$table][substr((string) $row['id'], 11)] = (string) $row['id'];
      }
    }
    return [$job, $ids];
  }

  /**
   * An output row, as WorkspaceClient reads it from "actividad".
   */
  private function row(array $ids, string $engine_id, string $template, string $group, string $teacher, string $room, int $day, int $slot): array {
    return [
      'id' => $engine_id,
      'plantilla' => $ids['plantilla_de_actividad'][$template],
      'grupo' => $ids['grupo'][$group],
      'profesor' => $ids['profesor'][$teacher],
      'lugar' => $ids['lugar'][$room],
      'dia' => (string) $day,
      'turno' => (string) $slot,
      'momento_de_colocacion' => '8463176.47',
      'creador' => ScopeMapper::creatorOf((int) $this->owner->id()),
    ];
  }

  /**
   * A returned run with the given output and the current input version.
   */
  private function returned(array $output, ?string $version = NULL): RunResult {
    $version ??= $this->container->get(WorkspaceSync::class)->currentVersion();
    return new RunResult(RunResult::RETURNED, input: ['site_version' => $version], output: $output, workspace: '1_1');
  }

  /**
   * Published once; publishing the same output again creates nothing.
   */
  public function testPublishAndReplay(): void {
    [$job, $ids] = $this->runningJob();
    $templates = array_keys($ids['plantilla_de_actividad']);
    sort($templates);
    $output = [
      $this->row($ids, 'ancoragoh-1-a-actividad', $templates[0], 'G1', 'TA', 'L1', 1, 1),
      $this->row($ids, 'ancoragoh-1-b-actividad', $templates[0], 'G2', 'TA', 'L1', 1, 4),
    ];
    $publisher = $this->container->get(PlacementPublisher::class);
    $this->assertSame(['published' => 2, 'already_published' => 0], $publisher->publish($job, $this->returned($output)));
    $this->assertSame(2, $this->recordCount('ancora_placement'));
    $placement = current($this->container->get('entity_type.manager')->getStorage('ancora_placement')->loadByProperties(['engine_id' => 'ancoragoh-1-b-actividad']));
    $values = [$placement->get('day')->value, $placement->get('slot')->value, $placement->get('job')->value];
    $this->assertSame(['1', '4', (string) $job->id], $values);
    $this->assertSame((int) $this->owner->id(), $placement->getCreatorId(), 'the owner is the plan creator (D1-D3)');

    // A replay (the same rows again, the input now including them).
    $this->assertSame(['published' => 0, 'already_published' => 2], $publisher->publish($job, $this->returned($output)));
    $this->assertSame(2, $this->recordCount('ancora_placement'));

    // The stored placements are the next input, in order, with their ids.
    [$rows] = $this->container->get(SiteRows::class)->site();
    $loaded = $this->container->get(SiteRows::class)->placements($rows);
    $this->assertSame(['ancoragoh-1-a-actividad', 'ancoragoh-1-b-actividad'], array_column($loaded, 'id'));
    $keys = ['plantilla', 'grupo', 'profesor', 'lugar'];
    $this->assertSame(array_intersect_key($output[0], array_flip($keys)), array_intersect_key($loaded[0], array_flip($keys)));
    $this->assertSame([1, 1], [$loaded[0]['dia'], $loaded[0]['turno']]);
  }

  /**
   * Data changed after the snapshot: nothing is published.
   */
  public function testStaleInputIsRefused(): void {
    [$job, $ids] = $this->runningJob();
    $version = $this->container->get(WorkspaceSync::class)->currentVersion();
    $teacher = $this->byCode('ancora_teacher', 'TA');
    $teacher->set('label', 'Renamed after the snapshot')->save();
    $templates = array_keys($ids['plantilla_de_actividad']);
    $this->assertPublicationFails($job, $this->returned([$this->row($ids, 'x-1', $templates[0], 'G1', 'TA', 'L1', 1, 1)], $version), PublicationException::STALE);
  }

  /**
   * An invalid row after two valid ones: the two are rolled back too.
   */
  public function testFailureHalfwayKeepsNothing(): void {
    [$job, $ids] = $this->runningJob();
    $templates = array_keys($ids['plantilla_de_actividad']);
    sort($templates);
    $output = [
      $this->row($ids, 'x-1', $templates[0], 'G1', 'TA', 'L1', 1, 1),
      $this->row($ids, 'x-2', $templates[0], 'G2', 'TA', 'L1', 1, 2),
      $this->row($ids, 'x-3', $templates[1], 'G1', 'TA', 'L1', 99, 1),
    ];
    $this->assertPublicationFails($job, $this->returned($output), PublicationException::INVALID);
  }

  /**
   * A teacher of another user, a duplicate pair, a fenced run.
   */
  public function testOwnershipDuplicatesAndFencing(): void {
    [$job, $ids] = $this->runningJob();
    $templates = array_keys($ids['plantilla_de_actividad']);
    $other = $this->createUser(['view academic data', 'edit academic data'], 'other');
    $foreign = $this->newRecord('ancora_teacher', ['uid' => $other->id(), 'label' => 'Foreign', 'code' => 'TF']);
    $foreign->save();
    $row = $this->row($ids, 'x-1', $templates[0], 'G1', 'TA', 'L1', 1, 1);
    $row['profesor'] = sprintf('%010d:TF', $foreign->id());
    $this->assertPublicationFails($job, $this->returned([$row]), PublicationException::INVALID);

    $twice = [
      $this->row($ids, 'x-1', $templates[0], 'G1', 'TA', 'L1', 1, 1),
      $this->row($ids, 'x-2', $templates[0], 'G1', 'TA', 'L1', 2, 1),
    ];
    $this->assertPublicationFails($job, $this->returned($twice), PublicationException::INVALID);

    $this->container->get(JobRepository::class)->finish($job, JobState::Failed, [], 'test');
    $this->assertPublicationFails($job, $this->returned([$this->row($ids, 'x-1', $templates[0], 'G1', 'TA', 'L1', 1, 1)]), PublicationException::FENCED);
  }

  /**
   * Deleting a teacher deletes its placements (legacy ON DELETE CASCADE).
   */
  public function testTeacherDeleteCascades(): void {
    [$job, $ids] = $this->runningJob();
    $templates = array_keys($ids['plantilla_de_actividad']);
    $this->container->get(PlacementPublisher::class)->publish($job, $this->returned([$this->row($ids, 'x-1', $templates[0], 'G1', 'TA', 'L1', 1, 1)]));
    $teacher = $this->byCode('ancora_teacher', 'TA');
    $storage = $this->container->get('entity_type.manager')->getStorage('ancora_teacher_assignment');
    $storage->delete($storage->loadByProperties(['teacher' => $teacher->id()]));
    $teacher->delete();
    $this->assertSame(0, $this->recordCount('ancora_placement'));
  }

  /**
   * Published right after a cache rebuild (cold caches), as after a deploy.
   *
   * The entity reference checks run entity queries; their access alter
   * computes the creator's permissions, which loads and caches the roles.
   * With the pgsql driver a cache write inside a query fails ("a transaction
   * named mimic_implicit_commit is already in use"), so the caches the
   * publication needs are warmed before its transaction.
   */
  public function testPublishWithColdCaches(): void {
    [$job, $ids] = $this->runningJob();
    $templates = array_keys($ids['plantilla_de_actividad']);
    sort($templates);
    $this->coldCaches();
    $output = [$this->row($ids, 'cold-1', $templates[0], 'G1', 'TA', 'L1', 1, 1)];
    $this->assertSame(['published' => 1, 'already_published' => 0], $this->container->get(PlacementPublisher::class)->publish($job, $this->returned($output)));
  }

  /**
   * The other resources of a placement: published, replayed, reloaded (D9d).
   *
   * The legacy otros_recursos_usados rows of a new activity are published
   * with it, in their order; a replay adds nothing; the next workspace
   * receives them after the placement (SiteRows::placements()); deleting a
   * resource removes it from the placement, which stays (ON DELETE CASCADE
   * of otros_recursos_usados only).
   */
  public function testOtherResourcesArePublishedAndReloaded(): void {
    [$job, $ids] = $this->runningJob();
    $templates = array_keys($ids['plantilla_de_actividad']);
    sort($templates);
    $resources = [];
    foreach (['R1', 'R2'] as $code) {
      $resource = $this->newRecord('ancora_other_resource', ['label' => "Projector $code", 'code' => $code]);
      $resource->save();
      $resources[$code] = $resource;
    }
    $eid = static fn (string $code): string => sprintf('%010d:%s', $resources[$code]->id(), $code);
    $uses = ['otros_recursos' => [$eid('R2'), $eid('R1')]];
    $row = $this->row($ids, 'res-1', $templates[0], 'G1', 'TA', 'L1', 1, 1) + $uses;
    $plain = $this->row($ids, 'res-2', $templates[0], 'G2', 'TA', 'L1', 1, 2) + ['otros_recursos' => []];
    $publisher = $this->container->get(PlacementPublisher::class);
    $result = $publisher->publish($job, $this->returned([$row, $plain]));
    $this->assertSame(['published' => 2, 'already_published' => 0], $result);
    $storage = $this->container->get('entity_type.manager')->getStorage('ancora_placement');
    $placement = current($storage->loadByProperties(['engine_id' => 'res-1']));
    $used = static fn ($p): array => array_map(static fn (array $i): int => (int) $i['target_id'], $p->get('other_resources')->getValue());
    $expected = [(int) $resources['R2']->id(), (int) $resources['R1']->id()];
    $this->assertSame($expected, $used($placement), 'in the order of their rows');
    $result = $publisher->publish($job, $this->returned([$row, $plain]));
    $this->assertSame(['published' => 0, 'already_published' => 2], $result);
    $this->assertSame(2, $this->recordCount('ancora_placement'));

    [$rows] = $this->container->get(SiteRows::class)->site();
    $loaded = array_column($this->container->get(SiteRows::class)->placements($rows), 'otros_recursos', 'id');
    $this->assertSame(['res-1' => [$eid('R2'), $eid('R1')], 'res-2' => []], $loaded);
    $before = $this->container->get(WorkspaceSync::class)->currentVersion();

    $resources['R2']->delete();
    $storage->resetCache();
    $placement = $storage->load($placement->id());
    $this->assertSame([(int) $resources['R1']->id()], $used($placement), 'the placement stays without the deleted resource');
    $this->assertNotSame($before, $this->container->get(WorkspaceSync::class)->currentVersion(), 'the resource uses are part of the input version');
  }

  /**
   * An other resource of another user is refused; the update path.
   */
  public function testForeignResourceAndUpdatePath(): void {
    [$job, $ids] = $this->runningJob();
    $templates = array_keys($ids['plantilla_de_actividad']);
    $other = $this->createUser(['view academic data', 'edit academic data'], 'other');
    $foreign = $this->newRecord('ancora_other_resource', ['uid' => $other->id(), 'label' => 'Foreign', 'code' => 'RF']);
    $foreign->save();
    $row = $this->row($ids, 'x-1', $templates[0], 'G1', 'TA', 'L1', 1, 1) + ['otros_recursos' => [sprintf('%010d:RF', $foreign->id())]];
    $this->assertPublicationFails($job, $this->returned([$row]), PublicationException::INVALID);

    $manager = $this->container->get('entity.definition_update_manager');
    $manager->uninstallFieldStorageDefinition($manager->getFieldStorageDefinition('other_resources', 'ancora_placement'));
    $this->container->get('module_handler')->loadInclude('ancora_scheduling', 'install');
    $this->assertSame('Installed the other resources of placements.', ancora_scheduling_update_11003());
    $this->assertSame('The other resources of placements are already installed.', ancora_scheduling_update_11003());
  }

  /**
   * Empties every cache, persistent and in memory.
   */
  private function coldCaches(): void {
    foreach (Cache::getBins() as $bin) {
      $bin->set('ancora_test_table', TRUE);
      $bin->deleteAll();
    }
    foreach (['entity.memory_cache', 'cache.access_policy_memory'] as $service) {
      $this->container->get($service)->deleteAll();
    }
    $this->container->get('config.factory')->reset();
  }

  /**
   * Asserts that a publication fails with a reason and leaves no placement.
   */
  private function assertPublicationFails(Job $job, RunResult $run, string $reason): void {
    try {
      $this->container->get(PlacementPublisher::class)->publish($job, $run);
      $this->fail("The publication did not fail ($reason).");
    }
    catch (PublicationException $e) {
      $this->assertSame($reason, $e->reason, $e->getMessage());
    }
    $this->assertSame(0, $this->recordCount('ancora_placement'), 'nothing was published');
  }

}
