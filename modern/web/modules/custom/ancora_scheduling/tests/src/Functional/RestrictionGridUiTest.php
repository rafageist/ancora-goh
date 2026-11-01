<?php

declare(strict_types=1);

namespace Drupal\Tests\ancora_scheduling\Functional;

use Drupal\ancora_academic\Fixture\SyntheticFixture;
use Drupal\Tests\ancora\Functional\AncoraBrowserTestBase;
use PHPUnit\Framework\Attributes\Group;

/**
 * The time restriction grid over HTTP (#70, D9a).
 *
 * Workspace mode, so that an occupied cell is a placement entity.
 */
#[Group('ancora_scheduling')]
final class RestrictionGridUiTest extends AncoraBrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['ancora_scheduling', 'block'];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $settings['settings']['ancora_engine'] = (object) [
      'value' => ['mode' => 'workspace'],
      'required' => TRUE,
    ];
    $this->writeSettings($settings);
  }

  /**
   * The id of a record by type and code.
   */
  private function id(string $entity_type_id, string $code): int {
    return (int) array_key_first(\Drupal::entityTypeManager()->getStorage($entity_type_id)->loadByProperties(['code' => $code]));
  }

  /**
   * Choose, check, save; an occupied cell is refused; others see nothing.
   */
  public function testGrid(): void {
    $owner = $this->drupalCreateUser(['view academic data', 'edit academic data']);
    $plan = \Drupal::service(SyntheticFixture::class)->load(TRUE, $owner);
    $period = $this->id('ancora_period', 'PER');
    $teacher = $this->id('ancora_teacher', 'TA');

    $this->drupalLogin($owner);
    // Reachable from the plan page and the teacher page.
    $this->drupalGet('/ancora/planning/' . $plan->id());
    $this->assertSession()->linkByHrefExists("/ancora/planning/restrictions?kind=period&period=$period");
    $this->drupalGet("/admin/ancora/teachers/$teacher");
    $this->assertSession()->linkByHrefExists("/ancora/planning/restrictions?kind=teacher&target=$teacher");

    // Choose the period and the teacher.
    $this->drupalGet('/ancora/planning/restrictions', ['query' => ['kind' => 'teacher']]);
    $this->submitForm(['period' => $period, 'kind' => 'teacher', 'target' => $teacher], 'Show');
    $this->assertSession()->pageTextContains('Restricted cells of Teacher Alpha in Week');
    // 5 days x 4 slots, with the hours of each slot.
    $this->assertSession()->elementsCount('css', 'table.ancora-restriction-grid tbody tr', 4);
    $this->assertSession()->pageTextContains('08:00–09:00');
    $this->submitForm(['grid[2][d1]' => TRUE, 'grid[1][d2]' => TRUE], 'Save restrictions');
    $this->assertSession()->pageTextContains('Saved the restrictions: 2 restricted cells.');
    $this->assertSession()->checkboxChecked('grid[2][d1]');
    $this->assertSession()->checkboxNotChecked('grid[1][d1]');
    $restrictions = \Drupal::entityTypeManager()->getStorage('ancora_time_restriction');
    $this->assertCount(2, $restrictions->loadByProperties(['kind' => 'teacher', 'teacher' => $teacher]));

    // A placement of the teacher at day 1, slot 1: that cell is refused.
    $etm = \Drupal::entityTypeManager();
    $etm->getStorage('ancora_placement')->create([
      'plan' => $plan->id(),
      'period' => $period,
      'template' => array_key_first($etm->getStorage('ancora_activity_template')->loadByProperties(['sequence' => 1])),
      'student_group' => $this->id('ancora_student_group', 'G1'),
      'teacher' => $teacher,
      'room' => $this->id('ancora_room', 'L1'),
      'day' => 1,
      'slot' => 1,
      'engine_id' => 'test-1',
      'job' => 1,
    ])->save();
    $this->submitForm(['grid[1][d1]' => TRUE], 'Save restrictions');
    $this->assertSession()->pageTextContains('Day 1, slot 1 cannot be restricted for Teacher Alpha: a placed activity occupies it.');
    $this->assertSession()->pageTextContains('Nothing was saved.');
    $restrictions->resetCache();
    $this->assertCount(2, $restrictions->loadByProperties(['kind' => 'teacher', 'teacher' => $teacher]));

    // The period itself.
    $this->drupalGet('/ancora/planning/restrictions', ['query' => ['kind' => 'period', 'period' => $period]]);
    $this->assertSession()->pageTextContains('Restricted cells of the period in Week');
    $this->submitForm(['grid[1][d1]' => TRUE], 'Save restrictions');
    $this->assertSession()->pageTextContains('Saved the restrictions: 1 restricted cells.');

    // Another planner: none of the owner's periods or teachers, and no grid
    // for them even by URL.
    $this->drupalLogin($this->drupalCreateUser(['view academic data', 'edit academic data']));
    $this->drupalGet('/ancora/planning/restrictions', [
      'query' => ['kind' => 'teacher', 'period' => $period, 'target' => $teacher],
    ]);
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->optionNotExists('period', (string) $period);
    $this->assertSession()->elementNotExists('css', 'table.ancora-restriction-grid');
    // A viewer without edit permission cannot open the page.
    $this->drupalLogin($this->drupalCreateUser(['view academic data']));
    $this->drupalGet('/ancora/planning/restrictions');
    $this->assertSession()->statusCodeEquals(403);
  }

}
