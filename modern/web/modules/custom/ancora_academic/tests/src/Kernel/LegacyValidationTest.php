<?php

declare(strict_types=1);

namespace Drupal\Tests\ancora_academic\Kernel;

use Drupal\ancora_academic\Export\AcademicExport;
use Drupal\ancora_academic\Export\LegacyRows;
use PHPUnit\Framework\Attributes\Group;

/**
 * Only the legacy column rules are validated (#70, D6).
 *
 * Legacy (cases 113, 136; legacy-parity-inventory.md PAR-014..016, 030):
 * brigada.matricula is any integer (default 0); asignatura.especialidad is
 * nullable; names are unbounded VARCHAR and records have no code; a group's
 * cohorts need not share its specialty and level.
 */
#[Group('ancora')]
final class LegacyValidationTest extends AcademicKernelTestBase {

  /**
   * Cohort enrolment and subject specialty, through the export too.
   */
  public function testCohortAndSubjectColumns(): void {
    $plan = $this->loadFixture();
    $of = [
      'specialty' => $this->byCode('ancora_specialty', 'E')->id(),
      'level' => $this->byCode('ancora_level', 'N')->id(),
    ];
    $cohort = $this->newRecord('ancora_cohort', ['label' => 'Cohort 3', 'code' => 'B3'] + $of);
    $this->assertSame('0', (string) $cohort->get('enrollment')->value, 'enrolment defaults to 0');
    $cohort->set('enrollment', -5);
    $this->assertSame([], $this->violationFields($cohort));
    $cohort->save();
    // Specialty and level of a cohort stay required (NOT NULL).
    $incomplete = $this->newRecord('ancora_cohort', ['label' => 'x', 'code' => 'B9']);
    $this->assertSame(['level', 'specialty'], $this->violationFields($incomplete));

    $subject = $this->newRecord('ancora_subject', [
      'label' => 'Subject without specialty',
      'code' => 'Z',
      'level' => $of['level'],
    ]);
    $this->assertSame([], $this->violationFields($subject));
    $subject->save();
    $no_level = $this->newRecord('ancora_subject', ['label' => 'x', 'code' => 'Y']);
    $this->assertSame(['level'], $this->violationFields($no_level));

    $rows = $this->container->get(LegacyRows::class)->build($this->container->get(AcademicExport::class)->export($plan));
    $subjects = array_column($rows['asignatura'], NULL, 'id');
    $this->assertNull($subjects['Z']['especialidad']);
    $this->assertSame('E', $subjects['A']['especialidad']);
    $this->assertSame(-5, array_column($rows['brigada'], NULL, 'id')['B3']['matricula']);
  }

  /**
   * The update widens the code and name columns of an earlier schema.
   */
  public function testUpdateWidensColumns(): void {
    $this->loadFixture();
    $installed = $this->container->get('entity.last_installed_schema.repository');
    $kv = $this->container->get('keyvalue')->get('entity.storage_schema.sql');
    $schema = $this->container->get('database')->schema();
    // The schema before #70 D6: code VARCHAR(32), label VARCHAR(128).
    foreach (['code' => 32, 'label' => 128] as $field => $length) {
      $definition = $installed->getLastInstalledFieldStorageDefinitions('ancora_teacher')[$field];
      $definition->setSetting('max_length', $length);
      $installed->setLastInstalledFieldStorageDefinition($definition);
      $key = "ancora_teacher.field_schema_data.$field";
      $data = $kv->get($key);
      $data['ancora_teacher']['fields'][$field]['length'] = $length;
      $kv->set($key, $data);
      $schema->changeField('ancora_teacher', $field, $field, $data['ancora_teacher']['fields'][$field]);
    }
    $this->assertSame(32, (int) $installed->getLastInstalledFieldStorageDefinitions('ancora_teacher')['code']->getSetting('max_length'));

    $this->container->get('module_handler')->loadInclude('ancora_academic', 'install');
    $message = ancora_academic_update_11004();
    $this->assertStringContainsString('2 name and code columns were widened', $message);
    $this->assertSame(255, (int) $installed->getLastInstalledFieldStorageDefinitions('ancora_teacher')['code']->getSetting('max_length'));
    $this->assertArrayNotHasKey('ancora_teacher', $this->container->get('entity.definition_update_manager')->getChangeSummary());

    // The existing values are kept, and a long code now fits.
    $this->assertSame('Teacher Alpha', (string) $this->byCode('ancora_teacher', 'TA')->label());
    $teacher = $this->newRecord('ancora_teacher', ['label' => str_repeat('n', 200), 'code' => str_repeat('c', 200)]);
    $this->assertSame([], $this->violationFields($teacher));
    $teacher->save();
    $this->assertStringContainsString('0 name and code columns', ancora_academic_update_11004());
  }

  /**
   * The update reports cohorts in two groups of one slot, and changes none.
   */
  public function testUpdateReportsDoubledCohorts(): void {
    $plan = $this->loadFixture();
    // As an earlier Drupal allowed: B1 in G1 and G2 (bypassing the move).
    $this->container->get('database')->insert('ancora_student_group__cohorts')->fields([
      'bundle' => 'ancora_student_group',
      'deleted' => 0,
      'entity_id' => $this->byCode('ancora_student_group', 'G2', $plan)->id(),
      'revision_id' => $this->byCode('ancora_student_group', 'G2', $plan)->id(),
      'langcode' => 'en',
      'delta' => 1,
      'cohorts_target_id' => $this->byCode('ancora_cohort', 'B1')->id(),
    ])->execute();
    $this->container->get('entity_type.manager')->getStorage('ancora_student_group')->resetCache();
    $this->container->get('module_handler')->loadInclude('ancora_academic', 'install');
    $this->assertStringContainsString('1 cohorts are in two groups', ancora_academic_update_11004());
    $this->assertCount(2, $this->byCode('ancora_student_group', 'G2', $plan)->get('cohorts'));
  }

}
