<?php

declare(strict_types=1);

namespace Drupal\Tests\ancora_academic\Kernel;

use Drupal\ancora_academic\Entity\Period;
use Drupal\ancora_academic\Entity\TimeRestriction;
use Drupal\ancora_academic\Export\AcademicExport;
use Drupal\ancora_academic\Export\LegacyRows;
use Drupal\ancora_academic\TimeRestrictionGrid;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;

/**
 * Time restrictions: the grid, the legacy rows, access and cascades (D9a).
 *
 * Legacy (legacy-parity-inventory.md PAR-070, PAR-071; case 139): one row
 * per restricted cell of a period, activity type, subject, brigade, teacher
 * or room, UNIQUE (object, period, day, slot), valor 1 as the legacy
 * editor stores it, por_actividad false for a manual restriction; deleted
 * with its object or period.
 */
#[CoversClass(TimeRestriction::class)]
#[CoversClass(TimeRestrictionGrid::class)]
#[Group('ancora')]
final class TimeRestrictionTest extends AcademicKernelTestBase {

  /**
   * The fixture period.
   */
  private function period(): Period {
    $period = $this->byCode('ancora_period', 'PER');
    assert($period instanceof Period);
    return $period;
  }

  /**
   * The grid saves the checked cells and frees the others; rows as legacy.
   */
  public function testGridAndLegacyRows(): void {
    $plan = $this->loadFixture();
    $grid = $this->container->get(TimeRestrictionGrid::class);
    $period = $this->period();
    $cells = [
      'period' => [NULL, [[1, 1], [2, 3]]],
      'teacher' => ['TA', [[1, 2]]],
      'cohort' => ['B1', [[3, 1]]],
      'subject' => ['A', [[4, 4]]],
      'activity_type' => ['C1', [[5, 1]]],
      'room' => ['L1', [[5, 2]]],
    ];
    foreach ($cells as $kind => [$code, $list]) {
      $target = $code === NULL ? NULL : (int) $this->byCode(TimeRestriction::TARGET_TYPES[$kind], $code)->id();
      $this->assertSame([], $grid->save($kind, $target, $period, $list), $kind);
    }
    $this->assertSame(['1:1', '2:3'], array_keys($grid->load('period', NULL, (int) $period->id())));

    $export = $this->container->get(AcademicExport::class)->export($plan);
    $rows = $this->container->get(LegacyRows::class)->build($export);
    // Every column but the id, as "column=value" pairs.
    $flat = static fn (array $list): array => array_map(static function (array $row): string {
      unset($row['id']);
      return implode(' ', array_map(static fn ($k, $v): string => $k . '=' . var_export($v, TRUE), array_keys($row), $row));
    }, $list);
    $occupancy = " valor=1 por_actividad=false actividad=NULL";
    $this->assertSame([
      "periodo='PER' dia=1 turno=1 valor=1",
      "periodo='PER' dia=2 turno=3 valor=1",
    ], $flat($rows['restriccion_de_periodo']));
    $this->assertSame(["profesor='TA' periodo='PER' dia=1 turno=2" . $occupancy], $flat($rows['restriccion_de_profesor']));
    $this->assertSame(["brigada='B1' periodo='PER' dia=3 turno=1" . $occupancy], $flat($rows['restriccion_de_brigada']));
    $this->assertSame(["lugar='L1' periodo='PER' dia=5 turno=2" . $occupancy], $flat($rows['restriccion_de_lugar']));
    $this->assertSame(["asignatura='A' periodo='PER' dia=4 turno=4 valor=1"], $flat($rows['restriccion_de_asignatura']));
    $this->assertSame(["clasificacion='C1' periodo='PER' dia=5 turno=1 valor=1"], $flat($rows['restriccion_de_clasif_act']));

    // A new save of the grid replaces the cells.
    $this->assertSame([], $grid->save('period', NULL, $period, [[2, 3], [5, 4]]));
    $this->assertSame(['2:3', '5:4'], array_keys($grid->load('period', NULL, (int) $period->id())));
    $this->assertSame([], $grid->save('period', NULL, $period, []));
    $this->assertSame([], $grid->load('period', NULL, (int) $period->id()));
  }

  /**
   * One record per object and cell; the object matches the kind.
   */
  public function testKeys(): void {
    $this->loadFixture();
    $period = $this->period();
    $teacher = $this->byCode('ancora_teacher', 'TA');
    $this->container->get(TimeRestrictionGrid::class)->save('teacher', (int) $teacher->id(), $period, [[1, 1]]);
    $values = [
      'plan' => $period->getPlanId(),
      'period' => $period->id(),
      'kind' => 'teacher',
      'teacher' => $teacher->id(),
      'day' => 1,
      'slot' => 1,
    ];
    $this->assertSame(['day'], $this->violationFields($this->newRecord('ancora_time_restriction', $values)));
    $this->assertSame([], $this->violationFields($this->newRecord('ancora_time_restriction', ['slot' => 2] + $values)));
    $this->assertNotSame([], $this->violationFields($this->newRecord('ancora_time_restriction', ['teacher' => NULL] + $values)));
    $period_with_teacher = $this->newRecord('ancora_time_restriction', ['kind' => 'period', 'slot' => 3] + $values);
    $this->assertNotSame([], $this->violationFields($period_with_teacher));
    $this->assertNotSame([], $this->violationFields($this->newRecord('ancora_time_restriction', ['kind' => 'specialty'] + $values)));
  }

  /**
   * The owner's records only; deleted with the object or the period.
   */
  public function testAccessAndCascade(): void {
    $this->loadFixture();
    $period = $this->period();
    $grid = $this->container->get(TimeRestrictionGrid::class);
    $grid->save('period', NULL, $period, [[1, 1]]);
    $grid->save('teacher', (int) $this->byCode('ancora_teacher', 'TB')->id(), $period, [[1, 2]]);
    $storage = $this->container->get('entity_type.manager')->getStorage('ancora_time_restriction');
    $count = static fn () => (int) $storage->getQuery()->accessCheck(TRUE)->count()->execute();
    $this->assertSame(2, $count());
    $restriction = $storage->load(1);
    $this->assertTrue($restriction->access('update', $this->owner));

    $other = $this->createUser(['view academic data', 'edit academic data']);
    $this->assertFalse($restriction->access('view', $other));
    $this->assertFalse($restriction->access('update', $other));
    $this->setCurrentUser($other);
    $this->assertSame(0, $count());
    // Another planner's object may not be restricted in my period, nor mine
    // in theirs (the D1-D3 creator rule).
    $foreign = $this->newRecord('ancora_teacher', ['label' => 'Foreign', 'code' => 'TF']);
    $foreign->save();
    $this->setCurrentUser($this->owner);
    $this->assertNotSame([], $grid->save('teacher', (int) $foreign->id(), $period, [[2, 2]]));

    // ON DELETE CASCADE: deleting Teacher Beta's assignments and the teacher
    // removes its restriction.
    $assignments = $this->container->get('entity_type.manager')->getStorage('ancora_teacher_assignment');
    $assignments->delete($assignments->loadByProperties(['teacher' => $this->byCode('ancora_teacher', 'TB')->id()]));
    $this->byCode('ancora_teacher', 'TB')->delete();
    $this->assertSame(1, (int) $storage->getQuery()->accessCheck(FALSE)->count()->execute());
  }

  /**
   * The update installs the entity type.
   */
  public function testUpdatePath(): void {
    $manager = $this->container->get('entity.definition_update_manager');
    $manager->uninstallEntityType($manager->getEntityType('ancora_time_restriction'));
    $this->container->get('module_handler')->loadInclude('ancora_academic', 'install');
    $this->assertStringContainsString('installed', ancora_academic_update_11005());
    $this->assertNotNull($manager->getEntityType('ancora_time_restriction'));
    $this->assertStringContainsString('already', ancora_academic_update_11005());
    $this->loadFixture();
    $grid = $this->container->get(TimeRestrictionGrid::class);
    $this->assertSame([], $grid->save('period', NULL, $this->period(), [[1, 1]]));
  }

}
