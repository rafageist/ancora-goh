<?php

declare(strict_types=1);

namespace Drupal\Tests\ancora_academic\Kernel;

use Drupal\ancora_academic\Entity\Period;
use Drupal\ancora_academic\Export\AcademicExport;
use Drupal\ancora_academic\Export\LegacyRows;
use Drupal\ancora_academic\PeriodInputs;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;

/**
 * Breaks, priority zones and daily load limits (D9b).
 *
 * Legacy (legacy-parity-inventory.md PAR-013, PAR-077, PAR-078; case 140):
 * receso (period, after slot, length), zpriori (only zones other than 1,
 * CHECK > 0), higiene_para_profesor / _brigada (a NOT NULL limit).
 */
#[CoversClass(PeriodInputs::class)]
#[Group('ancora')]
final class PeriodInputsTest extends AcademicKernelTestBase {

  /**
   * The fixture period.
   */
  private function period(): Period {
    $period = $this->byCode('ancora_period', 'PER');
    assert($period instanceof Period);
    return $period;
  }

  /**
   * Saves, legacy rows, slot hours.
   */
  public function testSavesAndLegacyRows(): void {
    $plan = $this->loadFixture();
    $inputs = $this->container->get(PeriodInputs::class);
    $period = $this->period();
    $type = (int) $this->byCode('ancora_activity_type', 'C1')->id();
    $teacher = (int) $this->byCode('ancora_teacher', 'TA')->id();
    $cohort = (int) $this->byCode('ancora_cohort', 'B1')->id();

    $this->assertSame([], $inputs->saveBreaks($period, [1 => 0.5, 2 => NULL, 3 => 0.25]));
    $this->assertSame([], $inputs->saveZones($type, $period, ['1:1' => 3, '1:2' => 1, '2:4' => 2]));
    $this->assertSame([], $inputs->saveLimits($period, 'teacher', [$teacher => 2]));
    $this->assertSame([], $inputs->saveLimits($period, 'cohort', [$cohort => -1]));
    // Zone 1 is not stored.
    $this->assertSame(['1:1' => 3, '2:4' => 2], $inputs->zones($type, $period->id()));
    // dame_hora_inicial_turno(): 8, then 9 + 0.5, then 10.5, then 11 + 0.75.
    $starts = array_map(static fn (int $s): float => $inputs->slotStart($period, $s), [1, 2, 3, 4]);
    $this->assertSame([8.0, 9.5, 10.5, 11.75], $starts);

    $rows = $this->container->get(LegacyRows::class)->build($this->container->get(AcademicExport::class)->export($plan));
    $this->assertSame([
      ['periodo' => 'PER', 'despues_del_turno' => 1, 'duracion' => 0.5],
      ['periodo' => 'PER', 'despues_del_turno' => 3, 'duracion' => 0.25],
    ], $rows['receso']);
    $this->assertSame([
      ['clasificacion' => 'C1', 'periodo' => 'PER', 'dia' => 1, 'turno' => 1, 'valor' => 3],
      ['clasificacion' => 'C1', 'periodo' => 'PER', 'dia' => 2, 'turno' => 4, 'valor' => 2],
    ], $rows['zpriori']);
    $this->assertSame([['periodo' => 'PER', 'profesor' => 'TA', 'gasto_maximo_diario' => 2]], $rows['higiene_para_profesor']);
    $this->assertSame([['periodo' => 'PER', 'brigada' => 'B1', 'gasto_maximo_diario' => -1]], $rows['higiene_para_brigada']);

    // A save replaces the editor's records.
    $this->assertSame([], $inputs->saveZones($type, $period, ['1:1' => 1]));
    $this->assertSame([], $inputs->zones($type, $period->id()));
    $this->assertSame([], $inputs->saveLimits($period, 'teacher', [$teacher => NULL]));
    $this->assertSame([], $inputs->limits($period->id(), 'teacher'));
    $this->assertSame([$cohort => -1], $inputs->limits($period->id(), 'cohort'));

    // Zone 0 is refused (CHECK > 0), and nothing changes.
    $this->assertNotSame([], $inputs->saveZones($type, $period, ['1:1' => 0, '1:2' => 4]));
    $this->assertSame([], $inputs->zones($type, $period->id()));
  }

  /**
   * Owner only; deleted with the period or object.
   */
  public function testAccessAndCascade(): void {
    $this->loadFixture();
    $inputs = $this->container->get(PeriodInputs::class);
    $period = $this->period();
    $inputs->saveBreaks($period, [1 => 0.5]);
    $inputs->saveLimits($period, 'teacher', [(int) $this->byCode('ancora_teacher', 'TB')->id() => 3]);
    $storage = $this->container->get('entity_type.manager')->getStorage('ancora_break');
    $count = static fn () => (int) $storage->getQuery()->accessCheck(TRUE)->count()->execute();
    $this->assertSame(1, $count());
    $other = $this->createUser(['view academic data', 'edit academic data']);
    $break = $storage->load(1);
    $this->assertFalse($break->access('view', $other));
    $this->assertFalse($break->access('update', $other));
    $this->setCurrentUser($other);
    $this->assertSame(0, $count());
    // Another planner's teacher cannot get a limit in this plan.
    $foreign = $this->newRecord('ancora_teacher', ['label' => 'Foreign', 'code' => 'TF']);
    $foreign->save();
    $this->setCurrentUser($this->owner);
    $this->assertNotSame([], $inputs->saveLimits($period, 'teacher', [(int) $foreign->id() => 1]));

    $assignments = $this->container->get('entity_type.manager')->getStorage('ancora_teacher_assignment');
    $assignments->delete($assignments->loadByProperties(['teacher' => $this->byCode('ancora_teacher', 'TB')->id()]));
    $this->byCode('ancora_teacher', 'TB')->delete();
    $this->assertSame([], $inputs->limits($period->id(), 'teacher'));
  }

  /**
   * The update installs the three entity types.
   */
  public function testUpdatePath(): void {
    $manager = $this->container->get('entity.definition_update_manager');
    foreach (['ancora_break', 'ancora_priority_zone', 'ancora_load_limit'] as $entity_type_id) {
      $manager->uninstallEntityType($manager->getEntityType($entity_type_id));
    }
    $this->container->get('module_handler')->loadInclude('ancora_academic', 'install');
    $this->assertStringContainsString('ancora_break, ancora_priority_zone, ancora_load_limit', ancora_academic_update_11006());
    $this->assertStringContainsString('none', ancora_academic_update_11006());
    $this->loadFixture();
    $this->assertSame([], $this->container->get(PeriodInputs::class)->saveBreaks($this->period(), [1 => 0.25]));
  }

}
