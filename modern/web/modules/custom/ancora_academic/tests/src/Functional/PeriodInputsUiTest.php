<?php

declare(strict_types=1);

namespace Drupal\Tests\ancora_academic\Functional;

use Drupal\ancora_academic\Fixture\SyntheticFixture;
use Drupal\ancora_academic\PeriodInputs;
use Drupal\Tests\ancora\Functional\AncoraBrowserTestBase;
use PHPUnit\Framework\Attributes\Group;

/**
 * The break, zone and load limit editors over HTTP (#70, D9b).
 */
#[Group('ancora')]
final class PeriodInputsUiTest extends AncoraBrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['ancora_academic', 'block'];

  /**
   * The id of a record by type and code.
   */
  private function id(string $entity_type_id, string $code): int {
    return (int) array_key_first(\Drupal::entityTypeManager()->getStorage($entity_type_id)->loadByProperties(['code' => $code]));
  }

  /**
   * The three editors; a second planner sees none of the data.
   */
  public function testEditors(): void {
    $owner = $this->drupalCreateUser(['view academic data', 'edit academic data']);
    \Drupal::service(SyntheticFixture::class)->load(TRUE, $owner);
    $period = $this->id('ancora_period', 'PER');
    $type = $this->id('ancora_activity_type', 'C1');
    $teacher = $this->id('ancora_teacher', 'TA');
    $cohort = $this->id('ancora_cohort', 'B2');
    $inputs = \Drupal::service(PeriodInputs::class);
    $this->drupalLogin($owner);

    // Breaks: one length per slot (4 slots: after 1, 2 or 3).
    $this->drupalGet('/ancora/planning/breaks');
    $this->submitForm(['period' => $period], 'Show');
    $this->assertSession()->elementsCount('css', 'table tbody tr', 3);
    $this->submitForm(['breaks[1][length]' => '0.5'], 'Save');
    $this->assertSession()->pageTextContains('Saved.');
    $this->assertSame([1], array_keys($inputs->breaks($period)));
    // The hours of the restriction grid include the break.
    $this->drupalGet('/ancora/planning/restrictions', ['query' => ['kind' => 'period', 'period' => $period]]);
    $this->assertSession()->pageTextContains('09:30–10:30');

    // Zones: the legacy grid of selects, 1..days x slots.
    $this->drupalGet('/admin/ancora/activity-types/' . $type);
    $this->clickLink('Priority zones');
    $this->submitForm(['period' => $period, 'activity_type' => $type], 'Show');
    $this->assertSession()->optionExists('zones[1][d1]', '20');
    $this->assertSession()->optionNotExists('zones[1][d1]', '21');
    $this->submitForm(['zones[1][d1]' => '3', 'zones[4][d5]' => '2'], 'Save');
    $this->assertSession()->pageTextContains('Saved.');
    $this->assertSame(['1:1' => 3, '5:4' => 2], $inputs->zones($type, $period));
    $this->assertSession()->fieldValueEquals('zones[1][d1]', '3');

    // Load limits: one number per teacher and cohort; empty is no limit.
    $this->drupalGet('/ancora/planning/load-limits', ['query' => ['period' => $period]]);
    $this->submitForm(["limits_teacher[$teacher][limit]" => '2', "limits_cohort[$cohort][limit]" => '0'], 'Save');
    $this->assertSession()->pageTextContains('Saved.');
    $this->assertSame([$teacher => 2], $inputs->limits($period, 'teacher'));
    $this->assertSame([$cohort => 0], $inputs->limits($period, 'cohort'));
    $this->submitForm(["limits_teacher[$teacher][limit]" => ''], 'Save');
    $this->assertSame([], $inputs->limits($period, 'teacher'));

    // Another planner: none of the owner's periods, no editor by URL.
    $this->drupalLogin($this->drupalCreateUser(['view academic data', 'edit academic data']));
    foreach (['breaks', 'priority-zones', 'load-limits'] as $path) {
      $this->drupalGet("/ancora/planning/$path", ['query' => ['period' => $period, 'activity_type' => $type]]);
      $this->assertSession()->statusCodeEquals(200);
      $this->assertSession()->optionNotExists('period', (string) $period);
      $this->assertSession()->buttonNotExists('Save');
    }
    $this->drupalLogin($this->drupalCreateUser(['view academic data']));
    $this->drupalGet('/ancora/planning/breaks');
    $this->assertSession()->statusCodeEquals(403);
  }

}
