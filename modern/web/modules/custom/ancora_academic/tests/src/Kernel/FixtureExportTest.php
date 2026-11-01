<?php

declare(strict_types=1);

namespace Drupal\Tests\ancora_academic\Kernel;

use Drupal\ancora_academic\Entity\Period;
use Drupal\ancora_academic\Export\AcademicExport;
use Drupal\ancora_academic\Export\LegacyRows;
use Drupal\ancora_academic\Fixture\SyntheticFixture;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;

/**
 * The export of the Entity API fixture is equivalent to dev/e2e/fixture.sql.
 *
 * The rows of fixture.sql are read from the repository and compared with the
 * legacy rows built from the export, so the two cannot drift apart silently.
 */
#[CoversClass(AcademicExport::class)]
#[CoversClass(LegacyRows::class)]
#[CoversClass(SyntheticFixture::class)]
#[Group('ancora')]
final class FixtureExportTest extends AcademicKernelTestBase {

  /**
   * Returns the export of the fixture plan, narrowed to period PER.
   *
   * @return array<string, mixed>
   *   The export.
   */
  private function exportFixture(bool $complete): array {
    $plan = $this->loadFixture($complete);
    $period = $this->byCode('ancora_period', 'PER', $plan);
    $this->assertInstanceOf(Period::class, $period);
    return $this->container->get(AcademicExport::class)->export($plan, $period);
  }

  /**
   * The export has the counts, references and order of the fixture.
   */
  public function testExportShape(): void {
    $export = $this->exportFixture(TRUE);
    $this->assertSame(AcademicExport::SCHEMA, $export['schema']);
    $this->assertSame('P', $export['plan']['code']);
    $this->assertSame(30, $export['plan']['duration_days']);
    $this->assertSame((int) $this->owner->id(), $export['owner']);
    $counts = array_map('count', array_diff_key($export, array_flip(['schema', 'owner', 'plan'])));
    $this->assertSame([
      'periods' => 1,
      'student_groups' => 2,
      'activity_templates' => 4,
      'activity_types' => 1,
      'specialties' => 1,
      'levels' => 1,
      'cohorts' => 2,
      'subjects' => 2,
      'rooms' => 2,
      'teachers' => 2,
      'other_resources' => 0,
      'travel_costs' => 0,
      'teacher_assignments' => 4,
      'room_assignments' => 8,
      'resource_groups' => 0,
      'breakdowns' => 2,
      'time_restrictions' => 0,
      'breaks' => 0,
      'priority_zones' => 0,
      'load_limits' => 0,
      'group_rooms' => 0,
      'issues' => 0,
    ], $counts);

    $this->assertSame(['G1', 'G2'], array_column($export['student_groups'], 'code'));
    $this->assertSame(
      [['A', 1], ['A', 2], ['B', 1], ['B', 2]],
      array_map(static fn (array $t): array => [$t['subject_code'], $t['sequence']], $export['activity_templates']),
    );
    $groups = array_column($export['student_groups'], 'key');
    foreach ($export['teacher_assignments'] as $assignment) {
      $this->assertSame($groups, $assignment['groups']);
    }
    $this->assertSame(['TA', 'TA', 'TB', 'TB'], array_column($export['teacher_assignments'], 'teacher_code'));
    $this->assertSame(['L1', 'L2', 'L1', 'L2', 'L1', 'L2', 'L1', 'L2'], array_column($export['room_assignments'], 'room_code'));

    // Keys are UUIDs, references resolve, and the output is deterministic.
    $keys = [];
    foreach ($export as $list) {
      if (is_array($list) && array_is_list($list)) {
        foreach ($list as $record) {
          if (isset($record['key']) && !isset($record['type'])) {
            $this->assertMatchesRegularExpression('/^[0-9a-f-]{36}$/', $record['key']);
            $keys[$record['key']] = TRUE;
          }
        }
      }
    }
    foreach ($export['activity_templates'] as $template) {
      $this->assertArrayHasKey($template['subject'], $keys);
      $this->assertArrayHasKey($template['activity_type'], $keys);
    }
    $plan = $this->byCode('ancora_plan', 'P');
    $again = $this->container->get(AcademicExport::class)->export($plan, $this->byCode('ancora_period', 'PER', $plan));
    $this->assertSame($export, $again);
    $this->assertJson(json_encode($export, JSON_THROW_ON_ERROR));
  }

  /**
   * Without the HTTP data entry steps, subject B has no teacher yet.
   */
  public function testIssuesOfTheFixtureOnly(): void {
    $export = $this->exportFixture(FALSE);
    $this->assertSame(['TA'], array_column($export['teachers'], 'code'));
    $this->assertSame(
      ['template_without_teacher', 'template_without_teacher'],
      array_column($export['issues'], 'type'),
    );
    $this->assertStringContainsString('B #1 (PER)', $export['issues'][0]['message']);
  }

  /**
   * The legacy rows of the fixture-only export equal dev/e2e/fixture.sql.
   */
  public function testLegacyRowsMatchFixtureSql(): void {
    $rows = $this->container->get(LegacyRows::class)->build($this->exportFixture(FALSE));
    $fixture = $this->parseFixtureSql();

    // Tables whose rows fixture.sql lists literally, with literal IDs.
    $literal = [
      'planificacion', 'periodo', 'clasificacion_de_actividad', 'especialidad',
      'nivel', 'brigada', 'grupo', 'brigada_de_grupo', 'asignatura',
      'profesor', 'lugar',
    ];
    // fixture.sql was written with SQL, not with the legacy UI: its period
    // hours are the meant ones (8, 1) and its plan takes the column default
    // activa TRUE. The rows carry what the legacy UI stores (PAR-010,
    // PAR-011, Q-20): the number of days as the start hour, the slot length
    // in minutes, activa NULL (case 146: the placements do not change).
    $fixture['periodo'][0]['hora_inicial'] = '5';
    $fixture['periodo'][0]['duracion_del_turno'] = '60';
    $this->assertSame([NULL], array_column($rows['planificacion'], 'activa'));
    foreach ($literal as $table) {
      $columns = array_keys($fixture[$table][0]);
      $this->assertSame($this->project($fixture[$table]), $this->project($rows[$table], $columns), $table);
    }
    // Generated IDs differ; compare the content.
    $this->assertSame(
      $this->project($fixture['periodicidad'], ['periodo', 'dia']),
      $this->project($rows['periodicidad'], ['periodo', 'dia']),
    );
    $this->assertSame(
      $this->project($fixture['profesor_para_actividad'], ['plantilla', 'profesor', 'prioridad']),
      $this->project($rows['profesor_para_actividad'], ['plantilla', 'profesor', 'prioridad']),
    );
    $resolve = static function (array $assignments, array $links): array {
      $by_id = array_column($assignments, NULL, 'id');
      return array_map(static fn (array $link): array => [
        'plantilla' => $by_id[$link['profesor_para_actividad']]['plantilla'],
        'profesor' => $by_id[$link['profesor_para_actividad']]['profesor'],
        'grupo' => $link['grupo'],
      ], $links);
    };
    $this->assertSame(
      $this->project($resolve($fixture['profesor_para_actividad'], $fixture['profesor_para_grupo'])),
      $this->project($resolve($rows['profesor_para_actividad'], $rows['profesor_para_grupo'])),
    );

    // Rows fixture.sql derives with triggers and INSERT ... SELECT: one
    // desglose per subject and period (trigger, legacy defaults), templates
    // <subject><orden> for orden 1..2 of class C1, and every room for every
    // template with priority 1 for L1 and 2 otherwise.
    $breakdowns = [];
    foreach ($rows['desglose'] as $r) {
      $breakdowns[] = array_values(array_diff_key($r, ['id' => TRUE]));
    }
    $this->assertSame([['A', 'PER', 1, 5, TRUE, FALSE], ['B', 'PER', 1, 5, TRUE, FALSE]], $breakdowns);
    $desglose = array_column($rows['desglose'], 'asignatura', 'id');
    $templates = [];
    foreach ($rows['plantilla_de_actividad'] as $t) {
      $t['desglose'] = $desglose[$t['desglose']];
      $templates[] = array_values($t);
    }
    $this->assertSame([
      ['A1', 'A', 1, 'C1', TRUE, TRUE],
      ['A2', 'A', 2, 'C1', TRUE, TRUE],
      ['B1', 'B', 1, 'C1', TRUE, TRUE],
      ['B2', 'B', 2, 'C1', TRUE, TRUE],
    ], $templates);
    $expected_rooms = [];
    foreach (['A1', 'A2', 'B1', 'B2'] as $template) {
      foreach (['L1' => 1, 'L2' => 2] as $room => $priority) {
        $expected_rooms[] = ['plantilla' => $template, 'lugar' => $room, 'prioridad' => $priority];
      }
    }
    $this->assertSame($expected_rooms, $rows['lugar_para_actividad']);
  }

  /**
   * The complete dataset adds Teacher Beta exactly as dev/e2e/lib.sh does.
   */
  public function testCompleteDatasetMatchesTheHttpHarness(): void {
    $export = $this->exportFixture(TRUE);
    $rows = $this->container->get(LegacyRows::class)->build($export, 'owner', TRUE);
    // Numbered ids: the entity id, ":" and the code.
    $teachers = array_column($export['teachers'], 'id', 'code');
    $tb = LegacyRows::engineId($teachers['TB'], 'TB');
    $this->assertSame([LegacyRows::engineId($teachers['TA'], 'TA'), $tb], array_column($rows['profesor'], 'id'));
    $this->assertSame(['owner'], array_values(array_unique(array_column($rows['profesor'], 'creador'))));
    $beta = array_values(array_filter($rows['profesor_para_actividad'], static fn (array $r): bool => $r['profesor'] === $tb));
    $this->assertSame(['B1', 'B2'], array_map(static fn (string $id): string => substr($id, 11), array_column($beta, 'plantilla')));
    // Ids sort in creation order, like the legacy replica ids.
    $groups = array_column($rows['grupo'], 'id');
    $sorted = $groups;
    sort($sorted, SORT_STRING);
    $this->assertSame($sorted, $groups);
    $this->assertMatchesRegularExpression('/^[0-9]{10}:G1$/', $groups[0]);
    $links = array_filter($rows['profesor_para_grupo'], static fn (array $r): bool => in_array($r['profesor_para_actividad'], array_column($beta, 'id'), TRUE));
    $this->assertCount(4, $links);
    // 4 templates x 2 groups = 8 demands, each with a teacher and a room.
    $this->assertCount(4, $rows['plantilla_de_actividad']);
    $this->assertCount(2, $rows['grupo']);
  }

  /**
   * The legacy day number is the plan form's TimeUtilsCommon::date_to_number.
   */
  public function testLegacyDayNumber(): void {
    // Values of the PHP function (lib/ancora/timeutils/TimeUtilsCommon.php).
    $this->assertSame(0, LegacyRows::legacyDayNumber('1999-12-31'));
    $this->assertSame(-669, LegacyRows::legacyDayNumber('1998-03-01'));
    $this->assertSame(9747, LegacyRows::legacyDayNumber('2026-09-07'));
    $this->assertSame(1, LegacyRows::legacyDayNumber('2000-01-01'));
    $this->assertSame(61, LegacyRows::legacyDayNumber('2000-03-01'));
    $this->assertSame(367, LegacyRows::legacyDayNumber('2001-01-01'));
    $this->expectException(\InvalidArgumentException::class);
    LegacyRows::legacyDayNumber('01/01/2000');
  }

  /**
   * A period of another plan is refused, and loading the fixture twice too.
   */
  public function testScopeAndRepeatability(): void {
    $plan = $this->loadFixture();
    $other = $this->newRecord('ancora_plan', ['label' => 'Other', 'code' => 'Q']);
    $other->save();
    $period = $this->newRecord('ancora_period', [
      'plan' => $other->id(),
      'label' => 'Week',
      'code' => 'PER',
      'days' => 5,
      'slots_per_day' => 4,
      'start_hour' => 8,
      'slot_duration' => 1,
    ]);
    $period->save();
    $this->assertInstanceOf(Period::class, $period);
    try {
      $this->container->get(AcademicExport::class)->export($plan, $period);
      $this->fail('A period of another plan was accepted.');
    }
    catch (\InvalidArgumentException $e) {
      $this->assertStringContainsString('does not belong', $e->getMessage());
    }
    // The other plan has its own periods, groups and templates, and shares
    // the catalogues of its creator (legacy PAR-003).
    $export = $this->container->get(AcademicExport::class)->export($other);
    $this->assertSame(['A', 'B'], array_column($export['subjects'], 'code'));
    $this->assertSame(['TA', 'TB'], array_column($export['teachers'], 'code'));
    $this->assertCount(1, $export['periods']);
    $this->assertSame([], $export['activity_templates']);
    $this->assertSame([], $export['student_groups']);

    // Another user's catalogue is empty.
    $other_user = $this->createUser(['view academic data', 'edit academic data']);
    $catalogue = $this->container->get(AcademicExport::class)->catalogue((int) $other_user->id());
    $this->assertNull($catalogue['plan']);
    $this->assertSame([], $catalogue['subjects']);

    $this->expectException(\RuntimeException::class);
    $this->expectExceptionMessage('already has a plan');
    $this->loadFixture();
  }

  /**
   * Parses the literal INSERT ... VALUES statements of dev/e2e/fixture.sql.
   *
   * @return array<string, list<array<string, int|float|string>>>
   *   Rows keyed by table.
   */
  private function parseFixtureSql(): array {
    $path = dirname(DRUPAL_ROOT, 2) . '/dev/e2e/fixture.sql';
    $this->assertFileExists($path);
    $sql = preg_replace('/--[^\n]*/', '', (string) file_get_contents($path));
    preg_match_all('/INSERT INTO (\w+) \(([^)]*)\)\s+VALUES\s+(.*?);/s', $sql, $statements, PREG_SET_ORDER);
    $tables = [];
    foreach ($statements as [, $table, $columns, $values]) {
      $columns = array_map('trim', explode(',', $columns));
      preg_match_all("/\\(((?:'[^']*'|[^()'])*)\\)/", $values, $tuples);
      foreach ($tuples[1] as $tuple) {
        preg_match_all("/'((?:[^']|'')*)'|(-?\\d+(?:\\.\\d+)?)/", $tuple, $fields, PREG_SET_ORDER);
        $row = array_map(static fn (array $f) => isset($f[2]) && $f[2] !== '' ? $f[2] + 0 : str_replace("''", "'", $f[1]), $fields);
        $this->assertCount(count($columns), $row, "a row of $table");
        $tables[$table][] = array_combine($columns, $row);
      }
    }
    $this->assertCount(14, $tables, 'fixture.sql has 14 literal INSERT ... VALUES statements');
    return $tables;
  }

  /**
   * Projects rows on columns, normalizes the values and sorts the rows.
   *
   * @param list<array<string, mixed>> $rows
   *   The rows.
   * @param list<string>|null $columns
   *   The columns to keep; all of them when NULL.
   *
   * @return list<string>
   *   One JSON line per row, sorted.
   */
  private function project(array $rows, ?array $columns = NULL): array {
    $lines = [];
    foreach ($rows as $row) {
      $projected = [];
      foreach ($columns ?? array_keys($row) as $column) {
        $value = $row[$column] ?? NULL;
        $projected[$column] = is_bool($value) ? ($value ? 't' : 'f') : (is_numeric($value) ? (string) ($value + 0) : $value);
      }
      $lines[] = json_encode($projected, JSON_THROW_ON_ERROR);
    }
    sort($lines);
    return $lines;
  }

}
