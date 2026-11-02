<?php

declare(strict_types=1);

namespace Drupal\Tests\ancora_scheduling\Functional;

use Drupal\ancora_engine\Job\JobOptions;
use Drupal\ancora_scheduling\Entity\Placement;
use Drupal\ancora_scheduling\ScopeMapper;
use Drupal\ancora_scheduling\Sync\WorkspaceSync;
use Drupal\ancora_scheduling\Timetable\PlacementModification;
use Drupal\Tests\ancora\Functional\AncoraBrowserTestBase;
use Drupal\Tests\ancora_scheduling\Traits\WorkspaceFixtureTrait;
use PHPUnit\Framework\Attributes\Group;

/**
 * Move and undo in place in the workspace, without JavaScript (#141 S1b).
 *
 * The move form of #130 in the inspector: every step posts to, and returns
 * to, the workspace URL with its context. The checks are finished by the
 * test (no worker here), as in PlacementMoveUiTest; the browser suite runs
 * the real worker.
 */
#[Group('ancora_scheduling')]
final class PlanWorkspaceMoveTest extends AncoraBrowserTestBase {

  use WorkspaceFixtureTrait;

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
  ];

  /**
   * The fixture.
   */
  private array $r;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $settings['settings']['ancora_engine'] = (object) ['value' => ['mode' => 'workspace'], 'required' => TRUE];
    $this->writeSettings($settings);
  }

  /**
   * The workspace path of plan A, and its query for teacher TC and A4.
   */
  private function a4(array $extra = []): array {
    return [
      '/ancora/planning/' . $this->r['plan:A']->id() . '/workspace',
      ['query' => ['lens' => 'teacher', 'obj' => $this->r['TC']->id(), 'sel' => $this->r['A4']->id()] + $extra],
    ];
  }

  /**
   * Finishes the latest check of a placement: every cell valid but $refused.
   */
  private function finishCheck(Placement $placement, array $refused = []): int {
    $storage = \Drupal::entityTypeManager()->getStorage('ancora_placement');
    $storage->resetCache();
    $placement = $storage->load($placement->id());
    $cells = [];
    for ($day = 1; $day <= 5; $day++) {
      for ($slot = 1; $slot <= 6; $slot++) {
        $reasons = $refused["$day.$slot"] ?? [];
        $cells[] = ['day' => $day, 'slot' => $slot, 'ok' => $reasons === [], 'reasons' => $reasons, 'warnings' => []];
      }
    }
    $db = \Drupal::database();
    $id = (int) $db->query('SELECT max(id) FROM {ancora_placement_check} WHERE placement_id = :p', [':p' => $placement->id()])->fetchField();
    $db->update('ancora_placement_check')->fields([
      'state' => 'done',
      'version' => \Drupal::service(WorkspaceSync::class)->currentVersion(),
      'signature' => PlacementModification::signature($placement),
      'result' => json_encode([
        'from' => ['day' => (int) $placement->get('day')->value, 'slot' => (int) $placement->get('slot')->value],
        'cells' => $cells,
      ]),
    ])->condition('id', $id)->execute();
    return $id;
  }

  /**
   * The current cell of a placement.
   */
  private function cell(string $key): string {
    $storage = \Drupal::entityTypeManager()->getStorage('ancora_placement');
    $storage->resetCache();
    $p = $storage->load($this->r[$key]->id());
    return $p->get('day')->value . '.' . $p->get('slot')->value;
  }

  /**
   * Find, progress, overlay, preview, move, undo: buttons only, in place.
   */
  public function testMoveAndUndoInTheWorkspace(): void {
    $planner = $this->drupalCreateUser(self::PLANNER);
    $this->r = $this->workspaceFixture((int) $planner->id());
    $this->drupalLogin($planner);
    $session = $this->assertSession();
    $page = $this->getSession()->getPage();

    $this->drupalGet(...$this->a4());
    $session->elementExists('css', '.ancora-inspector__move form#ancora-move-form[data-ancora-move-form]');
    $this->assertStringContainsString('/workspace?', $page->find('css', '#ancora-move-form')->getAttribute('action'));
    $session->elementNotExists('css', '.ancora-move__summary');
    $session->linkNotExists('Back to the timetable');

    // The check: queued, with the time waited; the URL keeps the context.
    $page->pressButton('Find the valid destinations');
    $url = $this->getSession()->getCurrentUrl();
    $this->assertMatchesRegularExpression('/\/workspace\?period=\d+&lens=teacher&obj=\d+&sel=\d+&check=\d+$/', $url);
    $session->elementExists('css', '[data-ancora-check-pending][data-ancora-check-state="queued"]');
    $session->elementTextContains('css', '[data-ancora-check-pending]', 'Queued: the destination check waits for the scheduler.');
    $session->elementExists('css', '[data-ancora-check-pending] [data-ancora-elapsed]');
    $session->elementNotExists('css', '#ancora-canvas-moving');

    // Done: the overlay and the list, the cross-plan warning per cell.
    $this->finishCheck($this->r['A4'], ['1.1' => ['room']]);
    $this->clickLink('Refresh');
    $session->elementTextContains('css', '#ancora-canvas-moving', 'Destinations of Mathematics');
    $session->elementTextContains('css', '#ancora-canvas-moving', '28 valid cells.');
    $session->elementExists('css', '.ancora-dest.is-here[data-ancora-dest="4.1"]');
    $session->elementTextContains('css', '.ancora-dest.is-refused[data-ancora-dest="1.1"]', 'its room is busy or restricted then');
    $session->elementExists('css', 'button.ancora-dest.is-valid.has-warning[data-ancora-dest="2.2"][form="ancora-move-form"][name="op_preview_2_2"]');
    $session->elementTextContains('css', '.ancora-dest[data-ancora-dest="2.2"]', 'room Room L2 busy in plan Intensive B, period Week B on 2026-09-15 10:00–10:30');
    $session->elementExists('css', '#ancora-move-grid input[name="op_preview_2_2"]');

    // Preview (the overlay's button belongs to the form), then the move.
    $page->find('css', '#ancora-move-grid input[name="op_preview_2_2"]')->press();
    $session->elementTextContains('css', '#ancora-move-preview', 'to day 2, slot 2');
    $session->elementTextContains('css', '#ancora-move-preview .ancora-move__cross-plan-list', 'Room Room L2');
    $session->elementExists('css', '.ancora-dest.is-preview[data-ancora-dest="2.2"]');
    $page->pressButton('Move here');
    $this->assertSame('2.2', $this->cell('A4'));
    $this->assertMatchesRegularExpression('/\/workspace\?period=\d+&lens=teacher&obj=\d+&sel=\d+(#ancora-move-feedback)?$/', $this->getSession()->getCurrentUrl());
    $session->elementTextContains('css', '#ancora-move-feedback[role="status"]', 'Moved it to day 2, slot 2.');
    $session->elementTextContains('css', '.ancora-wsbar__cross', '2 cross-plan overlaps in this period.');
    $session->elementTextContains('css', '[data-ancora-placement="' . $this->r['A4']->id() . '"]', '1 cross-plan warning');
    $session->elementNotExists('css', '#ancora-canvas-moving');

    // Undo: a new check; only the origin is offered.
    $page->pressButton('Undo the last move');
    $this->assertMatchesRegularExpression('/&check=\d+&undo=\d+$/', $this->getSession()->getCurrentUrl());
    $this->finishCheck($this->r['A4']);
    $this->clickLink('Refresh');
    $session->elementsCount('css', '.ancora-dest.is-valid', 1);
    $session->elementExists('css', '.ancora-dest.is-valid[data-ancora-dest="4.1"]');
    $session->elementTextContains('css', '#ancora-canvas-moving', 'Undo: where Mathematics');
    $page->find('css', '#ancora-move-grid input[name="op_preview_4_1"]')->press();
    $page->pressButton('Move it back here');
    $this->assertSame('4.1', $this->cell('A4'));
    $session->elementTextContains('css', '#ancora-move-feedback', 'Moved it back to day 4, slot 1.');
    $session->elementTextContains('css', '.ancora-wsbar__cross', 'One cross-plan overlap in this period.');
  }

  /**
   * Refusals in place: a run, a stale check, expired and failed checks.
   */
  public function testRefusals(): void {
    $planner = $this->drupalCreateUser(self::PLANNER);
    $this->r = $this->workspaceFixture((int) $planner->id());
    $this->drupalLogin($planner);
    $session = $this->assertSession();
    $page = $this->getSession()->getPage();
    $db = \Drupal::database();

    // A generation job queued meanwhile: refused, nothing moves.
    $this->drupalGet(...$this->a4());
    $page->pressButton('Find the valid destinations');
    $check = $this->finishCheck($this->r['A4']);
    $this->clickLink('Refresh');
    $page->find('css', '#ancora-move-grid input[name="op_preview_5_1"]')->press();
    $records = [$this->r['plan:A'], $this->r['period:A'], $this->r['E'], $this->r['N']];
    $jobs = \Drupal::service('ancora_engine.job_repository');
    $job = $jobs->enqueue(ScopeMapper::scope(...$records), new JobOptions(), 60, (int) $this->loggedInUser->id(), NULL, ScopeMapper::source(...$records));
    $page->pressButton('Move here');
    $session->elementTextContains('css', '#ancora-move-feedback[role="alert"]', "Timetable run $job is queued or running.");
    $this->assertSame('4.1', $this->cell('A4'));
    $this->assertMatchesRegularExpression("/&check=$check(#|$)/", $this->getSession()->getCurrentUrl());
    $db->delete('ancora_engine_job')->condition('id', $job)->execute();

    // A stale check (the data changed): refused, and a fresh check at once.
    $page->find('css', '#ancora-move-grid input[name="op_preview_5_1"]')->press();
    $this->r['TC']->set('label', 'Teacher TC renamed')->save();
    $page->pressButton('Move here');
    $session->elementTextContains('css', '#ancora-move-feedback[role="alert"]', 'changed after the destinations were checked');
    $session->elementTextContains('css', '#ancora-move-feedback[role="alert"]', 'A new check of the destinations has started.');
    $this->assertSame('4.1', $this->cell('A4'));
    $fresh = (int) $db->query('SELECT max(id) FROM {ancora_placement_check} WHERE placement_id = :p', [':p' => $this->r['A4']->id()])->fetchField();
    $this->assertGreaterThan($check, $fresh);
    $this->assertMatchesRegularExpression("/&check=$fresh(#|$)/", $this->getSession()->getCurrentUrl());
    $session->elementExists('css', '[data-ancora-check-pending]');

    // An expired check and a failed check: said, no overlay, check again.
    $this->finishCheck($this->r['A4']);
    $db->update('ancora_placement_check')->fields(['changed' => 1000])->condition('id', $fresh)->execute();
    $this->clickLink('Refresh');
    $session->pageTextContains('That check is too old. Check again.');
    $session->elementNotExists('css', '#ancora-canvas-moving');
    $session->buttonExists('Check again');
    $db->update('ancora_placement_check')->fields(['state' => 'failed', 'error' => 'test failure', 'changed' => time()])->condition('id', $fresh)->execute();
    $this->drupalGet(...$this->a4(['check' => $fresh]));
    $session->pageTextContains('The check failed: test failure');
    $session->elementNotExists('css', '#ancora-canvas-moving');
  }

  /**
   * The page and its regions fragment: both vary by X-Ancora-Regions.
   *
   * The same URL answers with the full page, or with the regions only when
   * the workspace script asks for them; both say so (Vary), both are not
   * cached, and they differ.
   */
  public function testVaryByRegionsHeader(): void {
    $planner = $this->drupalCreateUser(self::PLANNER);
    $this->r = $this->workspaceFixture((int) $planner->id());
    $this->drupalLogin($planner);
    $session = $this->getSession();
    [$path, $options] = $this->a4();
    $responses = [];
    foreach (['page' => [], 'fragment' => ['X-Ancora-Regions' => '1']] as $kind => $headers) {
      $responses[$kind] = $this->drupalGet($path, $options, $headers);
      $this->assertSession()->statusCodeEquals(200);
      $vary = array_map('trim', explode(',', (string) $session->getResponseHeader('Vary')));
      $this->assertContains('X-Ancora-Regions', $vary, "$kind: Vary");
      $this->assertStringContainsString('no-cache', (string) $session->getResponseHeader('Cache-Control'), "$kind: Cache-Control");
      $this->assertStringContainsString('private', (string) $session->getResponseHeader('Cache-Control'), "$kind: Cache-Control");
      // Where the dynamic page cache runs, it never stores either answer.
      $dynamic = $session->getResponseHeader('X-Drupal-Dynamic-Cache');
      if ($dynamic !== NULL) {
        $this->assertStringStartsWith('UNCACHEABLE', (string) $dynamic, "$kind: dynamic page cache");
      }
      $this->assertStringStartsWith('0', (string) $session->getResponseHeader('X-Drupal-Cache-Max-Age'), "$kind: max-age");
    }
    // The page has the shell; the fragment has only the regions.
    $this->assertStringContainsString('<html', $responses['page']);
    $this->assertStringContainsString('data-ancora-workspace', $responses['page']);
    $this->assertStringNotContainsString('<html', $responses['fragment']);
    $this->assertStringNotContainsString('data-ancora-workspace', $responses['fragment']);
    $this->assertSame(4, substr_count($responses['fragment'], 'data-ancora-region='));
    $this->assertNotSame($responses['page'], $responses['fragment']);
    // A page again, after the fragment: still the page (nothing cached).
    // Mink keeps request headers between requests, so say "not 1".
    $this->assertStringContainsString('<html', $this->drupalGet($path, $options, ['X-Ancora-Regions' => '0']));
    // The regions endpoint (GET) says the same.
    $this->drupalGet($path . '/regions', $options);
    $this->assertContains('X-Ancora-Regions', array_map('trim', explode(',', (string) $session->getResponseHeader('Vary'))));
  }

  /**
   * Access, forged values, two users and the form token.
   */
  public function testAccess(): void {
    $planner = $this->drupalCreateUser(self::PLANNER);
    $other = $this->drupalCreateUser(self::PLANNER);
    $reader = $this->drupalCreateUser(['view academic data']);
    $viewer = $this->drupalCreateUser(['view timetable viewer']);
    $this->r = $this->workspaceFixture((int) $planner->id());
    $o = $this->workspaceFixture((int) $other->id(), 'O');
    $session = $this->assertSession();
    $db = \Drupal::database();

    $this->drupalLogin($planner);
    $this->drupalGet(...$this->a4());
    $this->getSession()->getPage()->pressButton('Find the valid destinations');
    $check = $this->finishCheck($this->r['A4']);
    [$path, $options] = $this->a4(['check' => $check]);

    // Forged values: 404.
    foreach ([['check' => 'abc'], ['check' => '1 OR 1=1']] as $forged) {
      $this->drupalGet($path, ['query' => $forged + $options['query']]);
      $session->statusCodeEquals(404);
    }
    $this->drupalGet($path, ['query' => ['check' => $check]]);
    $session->statusCodeEquals(404);

    // Another planner: not the workspace, not its regions, not the move page;
    // its own workspace with this check id shows no check.
    $this->drupalLogin($other);
    foreach (['', '/regions'] as $suffix) {
      $this->drupalGet($path . $suffix, $options);
      $session->statusCodeEquals(403);
    }
    $this->drupalGet('/ancora/timetables/placements/' . $this->r['A4']->id() . '/move', ['query' => ['check' => $check]]);
    $session->statusCodeEquals(403);
    $own = ['sel' => $o['A1']->id(), 'check' => $check];
    $this->drupalGet('/ancora/planning/' . $o['plan:A']->id() . '/workspace', ['query' => $own]);
    $session->statusCodeEquals(200);
    $session->elementNotExists('css', '#ancora-canvas-moving');
    $session->buttonExists('Find the valid destinations');

    // A reader of other plans (view only, not the owner): refused.
    $this->drupalLogin($reader);
    $this->drupalGet($path, ['query' => ['sel' => $this->r['A4']->id()]]);
    $session->statusCodeEquals(403);
    // The viewer and anonymous: refused.
    $this->drupalLogin($viewer);
    $this->drupalGet($path, $options);
    $session->statusCodeEquals(403);
    $this->drupalLogout();
    $this->drupalGet($path, $options);
    $session->statusCodeEquals(403);

    // A step without a valid form token changes nothing.
    $this->drupalLogin($planner);
    $before = (int) $db->query('SELECT count(*) FROM {ancora_placement_check}')->fetchField();
    $this->drupalGet(...$this->a4());
    $page = $this->getSession()->getPage();
    $page->find('css', 'input[name="form_token"]')->setValue('forged');
    $page->pressButton('Find the valid destinations');
    $session->pageTextContains('The form has become outdated.');
    $this->assertSame($before, (int) $db->query('SELECT count(*) FROM {ancora_placement_check}')->fetchField());
  }

}
