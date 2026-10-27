<?php

declare(strict_types=1);

namespace Drupal\Tests\ancora_scheduling\Functional;

use Drupal\ancora_academic\Export\AcademicExport;
use Drupal\ancora_academic\Fixture\SyntheticFixture;
use Drupal\ancora_engine\Job\JobOptions;
use Drupal\ancora_engine\Job\JobState;
use Drupal\ancora_scheduling\ScopeMapper;
use Drupal\Tests\ancora\Functional\AncoraBrowserTestBase;
use PHPUnit\Framework\Attributes\Group;

/**
 * The legacy timetable explorer over HTTP (#119, D14, PAR-091).
 *
 * Workspace mode: the placements are entities, as PlacementPublisher makes
 * them. Kernel TimetableExplorerTest covers the selectors in depth; s14 and
 * r1-journey the two-database mode.
 */
#[Group('ancora_scheduling')]
final class ExplorerTest extends AncoraBrowserTestBase {

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
    $this->drupalPlaceBlock('system_menu_block:main', [
      'id' => 'main_nav',
      'region' => 'header',
      'level' => 1,
      'depth' => 1,
    ]);
    // The section navigation of config/sync: the children of the active item.
    $this->drupalPlaceBlock('system_menu_block:main', [
      'id' => 'section_nav',
      'region' => 'content',
      'level' => 2,
      'depth' => 1,
    ]);
  }

  /**
   * The id of the record of a type with a code.
   */
  private function id(string $type, string $code): int {
    return (int) array_key_first(\Drupal::entityTypeManager()->getStorage($type)->loadByProperties(['code' => $code]));
  }

  /**
   * Discoverable, per object, owner-only periods, no cache leak, no anonymous.
   */
  public function testExplorer(): void {
    $permissions = ['view academic data', 'edit academic data', 'view ancora engine jobs', 'request ancora engine jobs'];
    $alice = $this->drupalCreateUser($permissions);
    $plan = \Drupal::service(SyntheticFixture::class)->load(TRUE, $alice);
    $etm = \Drupal::entityTypeManager();
    $period = $this->id('ancora_period', 'PER');
    $specialty = $etm->getStorage('ancora_specialty')->load($this->id('ancora_specialty', 'E'));
    $level = $etm->getStorage('ancora_level')->load($this->id('ancora_level', 'N'));
    $period_entity = $etm->getStorage('ancora_period')->load($period);
    $jobs = \Drupal::service('ancora_engine.job_repository');
    $jobs->enqueue(ScopeMapper::scope($plan, $period_entity, $specialty, $level), new JobOptions(), 60, (int) $alice->id(), NULL, ScopeMapper::source($plan, $period_entity, $specialty, $level));
    $hash = ScopeMapper::hash(\Drupal::service(AcademicExport::class)->export($plan));
    $jobs->finish($jobs->claim('test'), JobState::Succeeded, ['kept' => 'placements', 'input_hash' => $hash]);
    $templates = $etm->getStorage('ancora_activity_template')->loadByProperties(['plan' => $plan->id()]);
    ksort($templates);
    $templates = array_values($templates);
    $rows = [[$templates[0], 'G1', 'TA', 'L1', 1, 1], [$templates[2], 'G2', 'TB', 'L2', 2, 3]];
    foreach ($rows as $i => [$template, $group, $teacher, $room, $day, $slot]) {
      $etm->getStorage('ancora_placement')->create([
        'plan' => $plan->id(),
        'period' => $period,
        'template' => $template->id(),
        'student_group' => $this->id('ancora_student_group', $group),
        'teacher' => $this->id('ancora_teacher', $teacher),
        'room' => $this->id('ancora_room', $room),
        'day' => $day,
        'slot' => $slot,
        'engine_id' => "x-$i",
        'job' => 1,
      ])->save();
    }
    $bob = $this->drupalCreateUser($permissions);
    $etm->getStorage('ancora_teacher')->create(['uid' => $bob->id(), 'label' => 'Teacher Zeta', 'code' => 'TZ'])->save();
    $ta = $this->id('ancora_teacher', 'TA');
    $b2 = $this->id('ancora_cohort', 'B2');

    // Discoverable: the main menu and the plan timetable link to it.
    $this->drupalLogin($alice);
    $this->drupalGet("/ancora/planning/{$plan->id()}/timetable");
    $this->clickLink('Explore by cohort, teacher, room or other resource');
    $this->assertSession()->addressEquals('/ancora/timetables/explorer');
    $this->assertSession()->pageTextContains('Pick a cohort, a teacher, a room or an other resource.');
    $this->assertSession()->linkExists('Timetable explorer');

    // By teacher, then by cohort.
    $url = '/ancora/timetables/explorer';
    $this->drupalGet($url, ['query' => ['period' => $period, 'teacher' => $ta]]);
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains('Teacher Alpha: 1 activities');
    $this->assertSession()->pageTextContains('Final timetable');
    $cell = $this->assertSession()->elementExists('css', 'td[data-day="1"][data-slot="1"] .ancora-placement');
    foreach (['Lecture', 'Subject Alpha', 'Teacher Alpha', 'Room 1'] as $text) {
      $this->assertStringContainsString($text, $cell->getText());
    }
    // Q-23: every planner's teachers are offered; nothing of Bob's period.
    $this->assertSession()->responseContains('Teacher Zeta');
    $this->drupalGet($url, ['query' => ['period' => $period, 'brigade' => $b2]]);
    $this->assertSession()->elementsCount('css', 'td[data-day] .ancora-placement', 1);
    $this->assertSession()->elementExists('css', 'td[data-day="2"][data-slot="3"] .ancora-placement');
    // Forged and wrong selectors are refused, nothing shown.
    $this->drupalGet($url, ['query' => ['period' => $period, 'teacher' => '999999']]);
    $this->assertSession()->pageTextContains('That record is not in the lists of this period.');
    $this->assertSession()->elementNotExists('css', '.ancora-placement');

    // The second planner, on the same URL: refused, nothing cached leaks.
    $this->drupalLogin($bob);
    $this->drupalGet($url, ['query' => ['period' => $period, 'teacher' => $ta]]);
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains('That period is not one of yours.');
    $this->assertSession()->elementNotExists('css', '.ancora-placement');
    $this->assertSession()->responseNotContains('Subject Alpha');
    // Bob has no period: the period list offers none of Alice's.
    $this->assertSession()->elementNotExists('css', 'select#ancora-explorer-period option[value="' . $period . '"]');

    // Anonymous visitors: no access.
    $this->drupalLogout();
    $this->drupalGet($url, ['query' => ['period' => $period, 'teacher' => $ta]]);
    $this->assertSession()->statusCodeEquals(403);
  }

}
