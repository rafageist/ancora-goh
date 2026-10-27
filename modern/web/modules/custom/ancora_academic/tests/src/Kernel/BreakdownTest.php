<?php

declare(strict_types=1);

namespace Drupal\Tests\ancora_academic\Kernel;

use Drupal\ancora_academic\BreakdownMaintainer;
use Drupal\ancora_academic\Entity\Breakdown;
use Drupal\ancora_academic\Entity\Period;
use Drupal\ancora_academic\Entity\Subject;
use Drupal\ancora_academic\Export\AcademicExport;
use Drupal\ancora_academic\Export\LegacyRows;
use Drupal\ancora_academic\Hook\BreakdownHooks;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;

/**
 * Tests the subject breakdowns (legacy "desglose", issue #70 D4).
 *
 * Legacy (legacy-parity-inventory.md PAR-031, PAR-032; cases 110, 135):
 * the triggers create one breakdown per subject and period of the same
 * creator, with the defaults 1, 5, TRUE, FALSE; the foreign keys delete it
 * with its subject or period; the user edits only the four options, and the
 * handler stores any integer.
 */
#[CoversClass(Breakdown::class)]
#[CoversClass(BreakdownMaintainer::class)]
#[CoversClass(BreakdownHooks::class)]
#[Group('ancora')]
final class BreakdownTest extends AcademicKernelTestBase {

  /**
   * Loads the breakdowns as "subject code@period code" => options.
   *
   * @return array<string, array<string, int|bool>>
   *   The legacy options of every breakdown.
   */
  private function breakdowns(): array {
    $storage = $this->container->get('entity_type.manager')->getStorage('ancora_breakdown');
    $storage->resetCache();
    $out = [];
    foreach ($storage->loadMultiple() as $breakdown) {
      assert($breakdown instanceof Breakdown);
      $out[$breakdown->getSubject()->getCode() . '@' . $breakdown->getPeriod()->getCode()] = $breakdown->legacyOptions();
    }
    ksort($out);
    return $out;
  }

  /**
   * The legacy defaults of a breakdown row.
   */
  private const DEFAULTS = ['min_dias' => 1, 'max_dias' => 5, 'respetar_orden' => TRUE, 'mismo_dia' => FALSE];

  /**
   * The insert triggers: every subject with every period of the same owner.
   */
  public function testCreatedLikeTheLegacyTriggers(): void {
    $plan = $this->loadFixture();
    $this->assertSame(['A@PER' => self::DEFAULTS, 'B@PER' => self::DEFAULTS], $this->breakdowns());

    // A second plan of the same owner: its period pairs with both subjects.
    $plan2 = $this->newRecord('ancora_plan', ['label' => 'Plan 2', 'code' => 'P2', 'duration_days' => 30]);
    $plan2->save();
    $period2 = $this->newRecord('ancora_period', [
      'plan' => $plan2->id(),
      'label' => 'Week 2',
      'code' => 'PER2',
      'days' => 5,
      'slots_per_day' => 4,
      'start_hour' => 8,
      'slot_duration' => 1,
    ]);
    $period2->save();
    // A new subject pairs with the periods of both plans.
    $subject = $this->newRecord('ancora_subject', [
      'label' => 'Subject Gamma',
      'code' => 'C',
      'specialty' => $this->byCode('ancora_specialty', 'E')->id(),
      'level' => $this->byCode('ancora_level', 'N')->id(),
    ]);
    $subject->save();
    $this->assertSame(['A@PER', 'A@PER2', 'B@PER', 'B@PER2', 'C@PER', 'C@PER2'], array_keys($this->breakdowns()));
    $this->assertSame([self::DEFAULTS], array_values(array_unique($this->breakdowns(), SORT_REGULAR)));

    // Another user's subject and plan are not paired with these.
    $other = $this->createUser(['view academic data', 'edit academic data']);
    $this->setCurrentUser($other);
    $specialty = $this->newRecord('ancora_specialty', ['label' => 'Other', 'code' => 'E']);
    $specialty->save();
    $level = $this->newRecord('ancora_level', ['label' => 'Other', 'code' => 'N']);
    $level->save();
    $foreign = $this->newRecord('ancora_subject', [
      'label' => 'Foreign',
      'code' => 'X',
      'specialty' => $specialty->id(),
      'level' => $level->id(),
    ]);
    $foreign->save();
    $this->assertCount(6, $this->breakdowns());

    // The breakdown belongs to the plan of its period.
    $breakdown = $this->container->get(BreakdownMaintainer::class)->find($subject->id(), $period2->id());
    $this->assertSame((int) $plan2->id(), $breakdown->getPlanId());
    $this->assertSame((int) $this->owner->id(), $breakdown->getCreatorId());
    $this->assertNotSame((int) $plan->id(), $breakdown->getPlanId());
  }

  /**
   * ON DELETE CASCADE: a breakdown never blocks deleting its subject.
   */
  public function testDeletedWithSubjectOrPeriod(): void {
    $this->loadFixture();
    $storage = $this->container->get('entity_type.manager');
    // A subject without templates, with an edited breakdown.
    $subject = $this->newRecord('ancora_subject', [
      'label' => 'Subject Gamma',
      'code' => 'C',
      'specialty' => $this->byCode('ancora_specialty', 'E')->id(),
      'level' => $this->byCode('ancora_level', 'N')->id(),
    ]);
    $subject->save();
    $period = $this->byCode('ancora_period', 'PER');
    $breakdown = $this->container->get(BreakdownMaintainer::class)->find($subject->id(), $period->id());
    $breakdown->set('min_days', 3)->save();
    $this->assertCount(3, $this->breakdowns());
    $subject->delete();
    $this->assertSame(['A@PER', 'B@PER'], array_keys($this->breakdowns()));
    $this->assertNull($storage->getStorage('ancora_breakdown')->load($breakdown->id()));
  }

  /**
   * The options accept every integer, as the legacy handler stores them.
   */
  public function testOptionsValidation(): void {
    $this->loadFixture();
    $breakdown = $this->container->get(BreakdownMaintainer::class)->find(
      $this->byCode('ancora_subject', 'A')->id(),
      $this->byCode('ancora_period', 'PER')->id(),
    );
    $this->assertSame([], $this->violationFields($breakdown));
    // Zero, negative values and a minimum above the maximum are stored
    // (set_Desglose.php has no check; case 135 shows their effect).
    foreach ([[0, 0], [-1, 5], [5, 1], [-2147483648, 2147483647]] as [$min, $max]) {
      $breakdown->set('min_days', $min)->set('max_days', $max);
      $this->assertSame([], $this->violationFields($breakdown), "$min, $max");
    }
    // Outside the PostgreSQL integer of the legacy column, or empty.
    $breakdown->set('min_days', 2147483648);
    $this->assertSame(['min_days'], $this->violationFields($breakdown));
    $breakdown->set('min_days', NULL);
    $this->assertSame(['min_days'], $this->violationFields($breakdown));
    // The subject and period pair is unique.
    $copy = $this->newRecord('ancora_breakdown', [
      'subject' => $breakdown->get('subject')->target_id,
      'period' => $breakdown->get('period')->target_id,
      'plan' => $breakdown->getPlanId(),
    ]);
    $this->assertNotSame([], $this->violationFields($copy));
  }

  /**
   * The owner views and edits; nobody creates or deletes; others see none.
   */
  public function testAccess(): void {
    $this->loadFixture();
    $other = $this->createUser(['view academic data', 'edit academic data']);
    $administrator = $this->createUser(['administer academic data']);
    $entity_type_manager = $this->container->get('entity_type.manager');
    $handler = $entity_type_manager->getAccessControlHandler('ancora_breakdown');
    $storage = $entity_type_manager->getStorage('ancora_breakdown');
    $breakdown = $storage->load(1);
    $this->assertInstanceOf(Breakdown::class, $breakdown);

    $this->assertTrue($breakdown->access('view', $this->owner));
    $this->assertTrue($breakdown->access('update', $this->owner));
    $this->assertFalse($breakdown->access('delete', $this->owner));
    $this->assertFalse($handler->createAccess(NULL, $this->owner));
    $this->assertFalse($breakdown->access('view', $other));
    $this->assertFalse($breakdown->access('update', $other));
    $this->assertTrue($breakdown->access('update', $administrator));

    $count = static fn () => (int) $storage->getQuery()->accessCheck(TRUE)->count()->execute();
    $this->assertSame(2, $count());
    $this->setCurrentUser($other);
    $this->assertSame(0, $count());
  }

  /**
   * The export and the legacy rows carry the edited options.
   */
  public function testExportCarriesTheOptions(): void {
    $plan = $this->loadFixture();
    $breakdown = $this->container->get(BreakdownMaintainer::class)->find(
      $this->byCode('ancora_subject', 'B')->id(),
      $this->byCode('ancora_period', 'PER')->id(),
    );
    $breakdown->set('min_days', 2)->set('max_days', 3)->set('respect_order', FALSE)->set('same_day', TRUE)->save();

    $export = $this->container->get(AcademicExport::class)->export($plan);
    $this->assertSame(['A', 'B'], array_column($export['breakdowns'], 'subject_code'));
    $rows = $this->container->get(LegacyRows::class)->build($export);
    $by_subject = array_column($rows['desglose'], NULL, 'asignatura');
    $this->assertSame(['id' => 'A@PER', 'asignatura' => 'A', 'periodo' => 'PER'] + self::DEFAULTS, $by_subject['A']);
    $this->assertSame([
      'id' => 'B@PER',
      'asignatura' => 'B',
      'periodo' => 'PER',
      'min_dias' => 2,
      'max_dias' => 3,
      'respetar_orden' => FALSE,
      'mismo_dia' => TRUE,
    ], $by_subject['B']);

    // A subject and period without a stored breakdown get the defaults.
    $breakdown->delete();
    $rows = $this->container->get(LegacyRows::class)->build($this->container->get(AcademicExport::class)->export($plan));
    $row = array_column($rows['desglose'], NULL, 'asignatura')['B'];
    $this->assertSame(self::DEFAULTS, array_diff_key($row, array_flip(['id', 'asignatura', 'periodo'])));
  }

  /**
   * The update path: existing records get breakdowns with the defaults.
   */
  public function testUpdatePath(): void {
    $this->loadFixture();
    $manager = $this->container->get('entity.definition_update_manager');
    $manager->uninstallEntityType($manager->getEntityType('ancora_breakdown'));
    $this->assertNull($manager->getEntityType('ancora_breakdown'));

    $this->container->get('module_handler')->loadInclude('ancora_academic', 'install');
    $message = ancora_academic_update_11003();
    $this->assertStringContainsString('2 subject breakdowns', $message);
    $this->assertNotNull($manager->getEntityType('ancora_breakdown'));
    $this->assertSame(['A@PER' => self::DEFAULTS, 'B@PER' => self::DEFAULTS], $this->breakdowns());
    // Running it again creates nothing.
    $this->assertStringContainsString('0 subject breakdowns', ancora_academic_update_11003());
    $this->assertCount(2, $this->breakdowns());
  }

  /**
   * A new owner of all records keeps one breakdown per pair.
   */
  public function testOwnerChange(): void {
    $plan = $this->loadFixture();
    $new = $this->createUser(['view academic data', 'edit academic data']);
    $plan->set('uid', $new->id())->save();
    foreach (['A', 'B'] as $code) {
      $subject = $this->byCode('ancora_subject', $code);
      assert($subject instanceof Subject);
      $subject->set('uid', $new->id())->save();
    }
    $this->assertSame(['A@PER', 'B@PER'], array_keys($this->breakdowns()));
    $period = $this->byCode('ancora_period', 'PER', $plan);
    assert($period instanceof Period);
    $this->assertSame((int) $new->id(), $this->container->get(BreakdownMaintainer::class)->find($this->byCode('ancora_subject', 'A', NULL, $new)->id(), $period->id())->getCreatorId());
  }

}
