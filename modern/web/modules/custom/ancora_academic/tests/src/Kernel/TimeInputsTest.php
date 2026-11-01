<?php

declare(strict_types=1);

namespace Drupal\Tests\ancora_academic\Kernel;

use Drupal\ancora_academic\Entity\Plan;
use Drupal\ancora_academic\Export\AcademicExport;
use Drupal\ancora_academic\Export\LegacyRows;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;

/**
 * Plan dates and period hours as the legacy UI stores them (PAR-010, 011).
 *
 * Legacy: fdm_Planificacion_fecha_inicial.php, ancoragoh_server_periodos.js
 * (Q-20), form_periodo.tpl; SQL case 146.
 */
#[CoversClass(Plan::class)]
#[CoversClass(LegacyRows::class)]
#[Group('ancora')]
final class TimeInputsTest extends AcademicKernelTestBase {

  /**
   * The two days give the length; the rows carry the legacy values.
   */
  public function testPlanDatesAndPeriodHours(): void {
    $plan = $this->newRecord('ancora_plan', [
      'label' => 'Autumn',
      'code' => 'AUT',
      'starts_on' => '2026-09-07',
      'ends_on' => '2027-01-04',
    ]);
    $this->assertSame([], $this->violationFields($plan));
    $plan->save();
    $this->assertSame(120, (int) $plan->get('duration_days')->value);
    // The last day before the first: no check, as legacy (cant_dias <= 0).
    $plan->set('ends_on', '2026-09-01')->save();
    $this->assertSame(-5, (int) $plan->get('duration_days')->value);
    $plan->set('ends_on', '2027-01-04')->save();

    $period = $this->newRecord('ancora_period', [
      'plan' => $plan->id(),
      'label' => 'Week',
      'code' => 'W',
      'days' => 5,
      'slots_per_day' => 6,
      'start_hour' => 9,
    ]);
    $this->assertSame([], $this->violationFields($period));
    $period->save();
    $this->assertSame(1.5, $period->getSlotDuration(), 'the legacy form default 1:30');

    assert($plan instanceof Plan);
    $rows = $this->container->get(LegacyRows::class)->build($this->container->get(AcademicExport::class)->export($plan));
    $this->assertSame([
      'id' => 'AUT',
      'descripcion' => 'Autumn',
      'fecha_inicial' => 9747,
      'cant_dias' => 120,
      'activa' => NULL,
      'creador' => 'admin-superuser',
    ], $rows['planificacion'][0]);
    $this->assertSame([5, 90], [$rows['periodo'][0]['hora_inicial'], $rows['periodo'][0]['duracion_del_turno']], 'Q-20: the days as the start hour; minutes');
  }

  /**
   * The update gives existing plans the last day of their current length.
   */
  public function testUpdateFillsTheLastDay(): void {
    $this->loadFixture();
    $values = ['label' => 'Dated', 'code' => 'D', 'starts_on' => '2026-09-07', 'duration_days' => 30];
    $dated = $this->newRecord('ancora_plan', $values);
    $dated->save();
    $this->container->get('database')->update('ancora_plan')->fields(['ends_on' => NULL])->execute();
    $this->container->get('module_handler')->loadInclude('ancora_academic', 'install');
    $this->assertSame('1 plans got their last day.', ancora_academic_update_11010());
    $storage = $this->container->get('entity_type.manager')->getStorage('ancora_plan');
    $storage->resetCache();
    $reloaded = $storage->load($dated->id());
    $got = [$reloaded->get('ends_on')->value, (int) $reloaded->get('duration_days')->value];
    $this->assertSame(['2026-10-06', 30], $got);
    $this->assertNull($this->byCode('ancora_plan', 'P')->get('ends_on')->value, 'the fixture plan has no first day and gets no last day');
    $this->assertSame('0 plans got their last day.', ancora_academic_update_11010());
  }

}
