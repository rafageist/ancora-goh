<?php

declare(strict_types=1);

namespace Drupal\Tests\ancora_scheduling\Functional;

use Drupal\ancora_academic\Export\AcademicExport;
use Drupal\ancora_academic\Fixture\SyntheticFixture;
use Drupal\ancora_engine\Job\JobOptions;
use Drupal\ancora_engine\Job\JobState;
use Drupal\ancora_scheduling\Entity\Placement;
use Drupal\ancora_scheduling\ScopeMapper;
use Drupal\ancora_scheduling\Sync\WorkspaceSync;
use Drupal\ancora_scheduling\Timetable\PlacementModification;
use Drupal\Tests\ancora\Functional\AncoraBrowserTestBase;
use PHPUnit\Framework\Attributes\Group;

/**
 * Moving one saved placement over HTTP, without JavaScript (#130).
 *
 * The keyboard and button path: find the destinations, choose a cell, check
 * the preview, move, undo. The worker's check is written into the check
 * table as PlacementCheckRunner writes it (the real check runs in
 * scripts/workspace-journey.sh).
 */
#[Group('ancora_scheduling')]
final class PlacementMoveUiTest extends AncoraBrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['ancora_scheduling', 'block'];

  /**
   * The planner's permissions.
   */
  private const PLANNER = [
    'view academic data',
    'edit academic data',
    'view ancora engine jobs',
    'request ancora engine jobs',
    'view timetable viewer',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->mode('workspace');
  }

  /**
   * Writes the engine mode into the site's settings.
   */
  private function mode(string $mode): void {
    $settings['settings']['ancora_engine'] = (object) ['value' => ['mode' => $mode], 'required' => TRUE];
    $this->writeSettings($settings);
  }

  /**
   * The id of a record with a code.
   */
  private function id(string $type, string $code): int {
    return (int) array_key_first(\Drupal::entityTypeManager()->getStorage($type)->loadByProperties(['code' => $code]));
  }

  /**
   * A planner's plan with two placements of a succeeded run.
   *
   * @return array
   *   The planner, the plan, the period id and the placements by group.
   */
  private function plan(): array {
    $planner = $this->drupalCreateUser(self::PLANNER);
    $plan = \Drupal::service(SyntheticFixture::class)->load(TRUE, $planner);
    $etm = \Drupal::entityTypeManager();
    $period = $this->id('ancora_period', 'PER');
    $records = [
      $plan,
      $etm->getStorage('ancora_period')->load($period),
      $etm->getStorage('ancora_specialty')->load($this->id('ancora_specialty', 'E')),
      $etm->getStorage('ancora_level')->load($this->id('ancora_level', 'N')),
    ];
    $jobs = \Drupal::service('ancora_engine.job_repository');
    $jobs->enqueue(ScopeMapper::scope(...$records), new JobOptions(), 60, (int) $planner->id(), NULL, ScopeMapper::source(...$records));
    $hash = ScopeMapper::hash(\Drupal::service(AcademicExport::class)->export($plan));
    $jobs->finish($jobs->claim('test'), JobState::Succeeded, ['kept' => 'placements', 'input_hash' => $hash]);
    $templates = $etm->getStorage('ancora_activity_template')->loadByProperties(['plan' => $plan->id()]);
    ksort($templates);
    $template = reset($templates);
    $placements = [];
    foreach (['G1' => [1, 1], 'G2' => [2, 3]] as $group => [$day, $slot]) {
      $placement = $etm->getStorage('ancora_placement')->create([
        'plan' => $plan->id(),
        'period' => $period,
        'template' => $template->id(),
        'student_group' => $this->id('ancora_student_group', $group),
        'teacher' => $this->id('ancora_teacher', 'TA'),
        'room' => $this->id('ancora_room', 'L1'),
        'day' => $day,
        'slot' => $slot,
        'engine_id' => "e-$group",
        'job' => 1,
      ]);
      $placement->save();
      $placements[$group] = $placement;
    }
    return [$planner, $plan, $period, $placements];
  }

  /**
   * Finishes the latest check of a placement as the worker would.
   *
   * @param \Drupal\ancora_scheduling\Entity\Placement $placement
   *   The placement.
   * @param array<string, list<string>> $refused
   *   Reasons by "day.slot".
   */
  private function finishCheck(Placement $placement, array $refused): void {
    $storage = \Drupal::entityTypeManager()->getStorage('ancora_placement');
    $storage->resetCache();
    $placement = $storage->load($placement->id());
    $cells = [];
    for ($day = 1; $day <= 5; $day++) {
      for ($slot = 1; $slot <= 4; $slot++) {
        $reasons = $refused["$day.$slot"] ?? [];
        $cells[] = [
          'day' => $day,
          'slot' => $slot,
          'ok' => $reasons === [],
          'reasons' => $reasons,
          'warnings' => $day === 5 ? ['teacher_load'] : [],
        ];
      }
    }
    $db = \Drupal::database();
    $id = $db->query('SELECT max(id) FROM {ancora_placement_check} WHERE placement_id = :p', [':p' => $placement->id()])->fetchField();
    $db->update('ancora_placement_check')->fields([
      'state' => 'done',
      'version' => \Drupal::service(WorkspaceSync::class)->currentVersion(),
      'signature' => PlacementModification::signature($placement),
      'result' => json_encode([
        'from' => ['day' => (int) $placement->get('day')->value, 'slot' => (int) $placement->get('slot')->value],
        'cells' => $cells,
      ]),
    ])->condition('id', $id)->execute();
  }

  /**
   * Find, choose, preview, move and undo, by buttons only.
   */
  public function testMoveAndUndoByButtons(): void {
    [$planner, $plan, $period, $placements] = $this->plan();
    $g1 = $placements['G1'];
    $this->drupalLogin($planner);

    // From the plan timetable.
    $this->drupalGet("/ancora/planning/{$plan->id()}/timetable", ['query' => ['period' => $period]]);
    $this->assertSession()->elementExists('css', 'a.ancora-placement__move[href$="/placements/' . $g1->id() . '/move"]');
    $this->drupalGet("/ancora/timetables/placements/{$g1->id()}/move");
    $this->assertSession()->pageTextContains('not checked against other plans whose dates overlap');
    $this->assertSession()->pageTextContains('Only the day and the first slot change');
    $this->assertSession()->pageTextNotContains('conflict-free');
    $this->assertSession()->pageTextContains('It was not moved by hand');

    // The check: pending (loading state), then done.
    $this->getSession()->getPage()->pressButton('Find the valid destinations');
    $this->assertSession()->elementExists('css', '[role="status"][aria-busy="true"][data-ancora-check-pending]');
    $this->assertSession()->linkExists('Refresh');
    $this->finishCheck($g1, ['2.3' => ['teacher', 'room'], '1.4' => ['overflow']]);
    $this->clickLink('Refresh');
    $this->assertSession()->buttonExists('op_preview_2_1');
    $this->assertSession()->elementExists('css', 'input[name="op_preview_2_3"][disabled]');
    $this->assertSession()->elementTextContains('css', '#ancora-move-cell-2-3-why', 'Not a destination: its teacher is busy or restricted then; its room is busy or restricted then.');
    $this->assertSession()->elementExists('css', 'input[name="op_preview_2_3"][aria-describedby="ancora-move-cell-2-3-why"]');
    $this->assertSession()->elementTextContains('css', '#ancora-move-cell-1-1-why', 'it is here now');
    $this->assertSession()->elementTextContains('css', '#ancora-move-cell-5-1-why', "Warning: over the teacher's daily load limit.");

    // Preview, then move.
    $this->getSession()->getPage()->pressButton('op_preview_2_1');
    $this->assertSession()->pageTextContains('to day 2, slot 1');
    $this->assertSession()->pageTextContains('Valid within this period; not checked against other plans whose dates overlap.');
    $this->getSession()->getPage()->pressButton('Move here');
    $this->assertSession()->elementTextContains('css', '#ancora-move-feedback[role="status"]', 'Moved it to day 2, slot 1.');
    $storage = \Drupal::entityTypeManager()->getStorage('ancora_placement');
    $storage->resetCache();
    $this->assertSame([2, 1], [
      (int) $storage->load($g1->id())->get('day')->value,
      (int) $storage->load($g1->id())->get('slot')->value,
    ]);
    $this->assertSession()->pageTextContains('day 1, slot 1 → day 2, slot 1');

    // The explorer marks it and links to the move; the viewer does neither.
    $query = ['period' => $period, 'teacher' => $this->id('ancora_teacher', 'TA')];
    $this->drupalGet('/ancora/timetables/explorer', ['query' => $query]);
    $this->assertSession()->pageTextContains('Moved by hand');
    $this->assertSession()->elementExists('css', 'a.ancora-placement__move');
    $this->drupalGet('/ancora/timetables/viewer', ['query' => $query]);
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextNotContains('Moved by hand');
    $this->assertSession()->elementNotExists('css', 'a.ancora-placement__move');

    // Undo: a new check, only the old cell is offered.
    $this->drupalGet("/ancora/timetables/placements/{$g1->id()}/move");
    $this->getSession()->getPage()->pressButton('Undo the last move');
    $this->finishCheck($g1, []);
    $this->clickLink('Refresh');
    $this->assertSession()->pageTextContains('Undo the last move');
    $this->assertSession()->elementExists('css', 'input[name="op_preview_3_1"][disabled]');
    $this->getSession()->getPage()->pressButton('op_preview_1_1');
    $this->getSession()->getPage()->pressButton('Move it back here');
    $this->assertSession()->elementTextContains('css', '#ancora-move-feedback[role="status"]', 'Moved it back to day 1, slot 1.');
    $this->assertSession()->pageTextContains('(undo)');
  }

  /**
   * Refusals over HTTP, access, and the two-database mode.
   */
  public function testRefusalsAndAccess(): void {
    [$planner, $plan, $period, $placements] = $this->plan();
    $g1 = $placements['G1'];
    $this->drupalLogin($planner);
    $this->drupalGet("/ancora/timetables/placements/{$g1->id()}/move");
    $this->getSession()->getPage()->pressButton('Find the valid destinations');
    $this->finishCheck($g1, []);
    $this->clickLink('Refresh');
    $this->getSession()->getPage()->pressButton('op_preview_3_2');

    // A generation job queued meanwhile: the move is refused, nothing moves.
    $etm = \Drupal::entityTypeManager();
    $records = [
      $plan,
      $etm->getStorage('ancora_period')->load($period),
      $etm->getStorage('ancora_specialty')->load($this->id('ancora_specialty', 'E')),
      $etm->getStorage('ancora_level')->load($this->id('ancora_level', 'N')),
    ];
    $job = \Drupal::service('ancora_engine.job_repository')->enqueue(ScopeMapper::scope(...$records), new JobOptions(), 60, (int) $planner->id(), NULL, ScopeMapper::source(...$records));
    $this->getSession()->getPage()->pressButton('Move here');
    $this->assertSession()->elementTextContains('css', '#ancora-move-feedback[role="alert"]', "Timetable run $job is queued or running.");
    $etm->getStorage('ancora_placement')->resetCache();
    $this->assertSame(1, (int) $etm->getStorage('ancora_placement')->load($g1->id())->get('day')->value);
    $this->assertSession()->pageTextContains("Timetable run $job is queued or running. You can check destinations");

    // Another planner, a viewer, anonymous, forged ids.
    $this->drupalLogin($this->drupalCreateUser(self::PLANNER));
    $this->drupalGet("/ancora/timetables/placements/{$g1->id()}/move");
    $this->assertSession()->statusCodeEquals(403);
    $this->drupalLogin($this->drupalCreateUser(['view academic data']));
    $this->drupalGet("/ancora/timetables/placements/{$g1->id()}/move");
    $this->assertSession()->statusCodeEquals(403);
    $this->drupalLogout();
    $this->drupalGet("/ancora/timetables/placements/{$g1->id()}/move");
    $this->assertSession()->statusCodeEquals(403);
    $this->drupalLogin($planner);
    $this->drupalGet('/ancora/timetables/placements/999999/move');
    $this->assertSession()->statusCodeEquals(404);
    $this->drupalGet("/ancora/timetables/placements/{$g1->id()}/move", [
      'query' => ['check' => '1 OR 1=1', 'undo' => 'x'],
    ]);
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->buttonExists('Find the valid destinations');

    // The two-database mode hides the editor and says why.
    $this->mode('two_database');
    $this->drupalGet("/ancora/timetables/placements/{$g1->id()}/move");
    $this->assertSession()->pageTextContains('Moving placements is offered in the workspace mode of the engine only.');
    $this->assertSession()->buttonNotExists('Find the valid destinations');
  }

}
