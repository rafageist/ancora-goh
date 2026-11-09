<?php

declare(strict_types=1);

namespace Drupal\Tests\ancora_scheduling\Kernel;

use Drupal\ancora_academic\Export\AcademicExport;
use Drupal\ancora_academic\Export\LegacyRows;
use Drupal\ancora_engine\Engine\EngineClient;
use Drupal\ancora_engine\EngineSettings;
use Drupal\ancora_engine\Job\JobOptions;
use Drupal\ancora_engine\Job\JobState;
use Drupal\ancora_scheduling\ScopeMapper;
use Drupal\ancora_scheduling\Timetable\RunActivities;
use Drupal\Core\Site\Settings;
use Drupal\Tests\ancora_academic\Kernel\AcademicKernelTestBase;
use PHPUnit\Framework\Attributes\Group;

/**
 * P2-c demand parity with the engine's actual METRICS demand CTE.
 */
#[Group('ancora_scheduling')]
final class RunActivitiesTest extends AcademicKernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'ancora_academic', 'ancora_engine', 'ancora_scheduling',
    'datetime', 'system', 'user', 'views',
  ];

  /**
   * The SQL metrics query and the exported rows identify identical demands.
   */
  public function testDemandCteAndCase063PartialPairs(): void {
    $plan = $this->loadFixture();
    $period = $this->byCode('ancora_period', 'PER', $plan);
    $scope = ScopeMapper::scope($plan, $period, $this->byCode('ancora_specialty', 'E'), $this->byCode('ancora_level', 'N'));
    $export = $this->container->get(AcademicExport::class)->export($plan);
    $rows = $this->container->get(LegacyRows::class)->build($export, ScopeMapper::creator($plan), TRUE);
    $this->assertCount(8, RunActivities::demands($rows, $scope));

    $db = $this->container->get('database');
    $columns = [
      'periodo' => 'id text, planificacion text, cant_turnos integer',
      'asignatura' => 'id text, especialidad text, nivel text',
      'desglose' => 'id text, asignatura text, periodo text',
      'plantilla_de_actividad' => 'id text, desglose text, clasificacion text',
      'grupo' => 'id text, clasificacion text, especialidad text, nivel text, planificacion text',
      'brigada_de_grupo' => 'brigada text, grupo text',
      'clasificacion_de_actividad' => 'id text, duracion integer',
      'actividad' => 'id text, plantilla text, grupo text, profesor text, lugar text, dia integer, turno integer',
    ];
    foreach ($columns as $table => $definition) {
      $db->query("CREATE TEMP TABLE $table ($definition)");
      $names = array_map(static fn (string $column): string => explode(' ', trim($column))[0], explode(',', $definition));
      foreach ($rows[$table] ?? [] as $row) {
        $values = array_intersect_key($row, array_flip($names));
        $columns_sql = implode(', ', array_keys($values));
        $parameters = array_map(static fn (string $name): string => ':' . $name, array_keys($values));
        $db->query("INSERT INTO $table ($columns_sql) VALUES (" . implode(', ', $parameters) . ')', array_combine($parameters, array_values($values)));
      }
    }
    [$specialty, $level, $period_id] = $scope->toParameters();
    $sql = (new \ReflectionClass(EngineClient::class))->getConstant('METRICS');
    $sql = str_replace(['$1', '$2', '$3'], [':specialty', ':level', ':period'], $sql);
    $args = [':specialty' => $specialty, ':level' => $level, ':period' => $period_id];
    $metrics = $db->query($sql, $args)->fetchAssoc();
    $this->assertSame(8, (int) $metrics['demands']);

    // Case 063 has two templates and two groups. G1 is restricted at every
    // cell, so the engine retains just the two G2 pairs (2 of 4 placed).
    $rows['plantilla_de_actividad'] = array_slice($rows['plantilla_de_actividad'], 0, 2);
    $demands = RunActivities::demands($rows, $scope);
    $this->assertCount(4, $demands);
    $db->query('DELETE FROM plantilla_de_actividad WHERE id NOT IN (:first, :second)', [
      ':first' => $rows['plantilla_de_actividad'][0]['id'],
      ':second' => $rows['plantilla_de_actividad'][1]['id'],
    ]);
    $metrics = $db->query($sql, $args)->fetchAssoc();
    $this->assertSame(4, (int) $metrics['demands']);
    $this->assertSame(4, (int) $metrics['unplaced']);

    $group = $rows['grupo'][1]['id'];
    $placements = [];
    foreach ($rows['plantilla_de_actividad'] as $i => $template) {
      $values = [
        'id' => 'placed' . $i,
        'plantilla' => $template['id'],
        'grupo' => $group,
        'profesor' => NULL,
        'lugar' => NULL,
        'dia' => 1,
        'turno' => $i + 1,
      ];
      $db->query('INSERT INTO actividad (id, plantilla, grupo, profesor, lugar, dia, turno) VALUES (:id, :plantilla, :grupo, :profesor, :lugar, :dia, :turno)', $values);
      $placements[] = ['template_id' => (int) substr($template['id'], 0, 10), 'group_id' => (int) substr($group, 0, 10)];
    }
    $metrics = $db->query($sql, $args)->fetchAssoc();
    $unplaced = RunActivities::unplaced($demands, $placements);
    $this->assertSame(2, (int) $metrics['unplaced']);
    $this->assertCount(2, $unplaced);
    $this->assertSame(['Subject Alpha #1', 'Subject Alpha #2'], array_column(array_values($unplaced), 'template'));
    foreach ($unplaced as $activity) {
      $this->assertNotSame($group, $activity['group_id']);
    }
  }

  /**
   * The current retained set is usable for partial and soft-cancelled runs.
   */
  public function testRetainedStaleAndOverflowOutcomes(): void {
    $this->container->set('ancora_engine.settings', new EngineSettings(new Settings([
      'ancora_engine' => ['mode' => 'workspace'],
    ])));
    $this->installSchema('ancora_engine', ['ancora_engine_job']);
    $this->installEntitySchema('ancora_placement');
    $plan = $this->loadFixture();
    $period = $this->byCode('ancora_period', 'PER', $plan);
    $specialty = $this->byCode('ancora_specialty', 'E');
    $level = $this->byCode('ancora_level', 'N');
    $scope = ScopeMapper::scope($plan, $period, $specialty, $level);
    $source = ScopeMapper::source($plan, $period, $specialty, $level);
    $jobs = $this->container->get('ancora_engine.job_repository');
    $reader = $this->container->get(RunActivities::class);
    $hash = ScopeMapper::hash($this->container->get(AcademicExport::class)->export($plan));
    $finish = static function (JobState $state, array $result) use ($jobs, $scope, $source): object {
      $id = $jobs->enqueue($scope, new JobOptions(), 60, 2, NULL, $source);
      $jobs->finish($jobs->claim('test'), $state, $result);
      return $jobs->load($id);
    };
    $partial = $finish(JobState::Partial, ['kept' => 'partial', 'input_hash' => $hash, 'demands' => 8, 'unplaced' => 7]);
    $subject = $this->byCode('ancora_subject', 'A', $plan);
    $templates = $this->container->get('entity_type.manager')->getStorage('ancora_activity_template')->loadByProperties([
      'subject' => $subject->id(),
      'period' => $period->id(),
      'sequence' => 1,
    ]);
    $this->assertCount(1, $templates);
    $template = reset($templates);
    $group = $this->byCode('ancora_student_group', 'G1', $plan);
    $placement = $this->container->get('entity_type.manager')->getStorage('ancora_placement')->create([
      'plan' => $plan->id(),
      'period' => $period->id(),
      'template' => $template->id(),
      'student_group' => $group->id(),
      'day' => 1,
      'slot' => 1,
      'engine_id' => 'p2c-retained',
      'job' => $partial->id,
    ]);
    $placement->save();
    $build = $reader->build($partial, $plan, $period);
    $this->assertStringContainsString('7 activities', (string) $build['intro']['#value']);
    $this->assertCount(7, array_filter(array_keys($build['table']), 'is_int'));
    $this->assertArrayNotHasKey('mismatch', $build);
    foreach ($build['table'] as $row) {
      if (is_array($row) && isset($row['template'], $row['group'])) {
        $this->assertFalse($row['template']['#plain_text'] === 'Subject Alpha #1' && $row['group']['#plain_text'] === 'Group 1');
      }
    }

    $cancelled = $finish(JobState::Cancelled, ['kept' => 'partial', 'input_hash' => $hash, 'unplaced' => 7]);
    $build = $reader->build($cancelled, $plan, $period);
    $this->assertCount(7, array_filter(array_keys($build['table']), 'is_int'));
    $this->assertArrayHasKey('unavailable', $reader->build($partial, $plan, $period), 'superseded placements are not a historical snapshot');

    $without_metrics = $finish(JobState::Cancelled, ['kept' => 'partial', 'input_hash' => $hash]);
    $build = $reader->build($without_metrics, $plan, $period);
    $this->assertCount(7, array_filter(array_keys($build['table']), 'is_int'));
    $this->assertArrayNotHasKey('mismatch', $build, 'missing metrics must not be invented');

    $room = $this->byCode('ancora_room', 'L1');
    $room->set('capacity', 60)->save();
    $this->assertArrayHasKey('stale', $reader->build($without_metrics, $plan, $period));
    $failed = $finish(JobState::Failed, ['kept' => 'none']);
    $this->assertStringContainsString('8 current activities', (string) $reader->build($failed, $plan, $period)['intro']['#value']);
    $timeout = $finish(JobState::TimedOut, ['kept' => 'none']);
    $this->assertArrayHasKey('stale', $reader->build($timeout, $plan, $period));

    $type = $this->byCode('ancora_activity_type', 'C1');
    $type->set('duration_slots', 2)->save();
    $placement->set('slot', 4)->save();
    $current_hash = ScopeMapper::hash($this->container->get(AcademicExport::class)->export($plan));
    $succeeded = $finish(JobState::Succeeded, ['kept' => 'placements', 'input_hash' => $current_hash, 'overflow' => 1]);
    $build = $reader->build($succeeded, $plan, $period);
    $this->assertCount(1, array_filter(array_keys($build['table']), 'is_int'));
    $this->assertSame('Subject Alpha #1', $build['table'][0]['template']['#plain_text']);
  }

}
