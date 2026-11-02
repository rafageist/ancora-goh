<?php

declare(strict_types=1);

namespace Drupal\Tests\ancora_scheduling\Functional;

use Drupal\ancora_scheduling\Workspace\ResumeStore;
use Drupal\Tests\ancora\Functional\AncoraBrowserTestBase;
use Drupal\Tests\ancora_scheduling\Traits\WorkspaceFixtureTrait;
use PHPUnit\Framework\Attributes\Group;

/**
 * The planning workspace over HTTP (#141, S1a): read-only, access, Resume.
 *
 * The workspace fixture (WorkspaceFixtureTrait) for a planner and for a
 * second owner; a viewer holds only "view timetable viewer".
 */
#[Group('ancora_scheduling')]
final class PlanWorkspaceUiTest extends AncoraBrowserTestBase {

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
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $settings['settings']['ancora_engine'] = (object) ['value' => ['mode' => 'workspace'], 'required' => TRUE];
    $this->writeSettings($settings);
  }

  /**
   * The path of a plan's workspace.
   */
  private function path(mixed $plan, string $suffix = ''): string {
    return '/ancora/planning/' . $plan->id() . '/workspace' . $suffix;
  }

  /**
   * The regions, the blocks and their cross-plan state; the legacy routes.
   */
  public function testRegions(): void {
    $planner = $this->drupalCreateUser(self::PLANNER);
    $r = $this->workspaceFixture((int) $planner->id());
    $this->drupalLogin($planner);
    $session = $this->assertSession();

    $query = ['lens' => 'teacher', 'obj' => $r['TA']->id()];
    $this->drupalGet($this->path($r['plan:A']), ['query' => $query]);
    $session->statusCodeEquals(200);
    foreach (['bar', 'nav', 'canvas', 'inspector'] as $region) {
      $session->elementExists('css', "[data-ancora-region=\"$region\"]");
    }
    $session->elementTextEquals('css', '#ancora-canvas-title', 'Teacher TA: 2 activities');
    $session->elementTextContains('css', '.ancora-wsbar__cross', 'One cross-plan overlap in this period.');
    // A2 (2 slots across the break) is ONE block that says where its break
    // is; A1 carries its cross-plan warning.
    $a2 = '[data-ancora-placement="' . $r['A2']->id() . '"]';
    $session->elementsCount('css', $a2, 1);
    $session->elementTextContains('css', $a2, '08:00–11:30 · 2 slots');
    $session->elementTextContains('css', $a2, 'break 09:30–10:00');
    $session->elementsCount('css', "$a2 .ancora-block__break", 1);
    $this->assertStringContainsString('--r1: 2; --r2: 5;', $this->getSession()->getPage()->find('css', $a2)->getAttribute('style'));
    $session->elementTextContains('css', '[data-ancora-placement="' . $r['A1']->id() . '"]', '1 cross-plan warning');
    // U10's accepted exception: inline styles hold only CSS custom
    // properties (the dynamic grid geometry), never a presentation rule.
    $styled = $this->getSession()->getPage()->findAll('css', '[data-ancora-workspace] [style]');
    $this->assertNotEmpty($styled);
    foreach ($styled as $element) {
      $this->assertMatchesRegularExpression('/^(\s*--[a-z0-9-]+:\s*[^;:]+;)+\s*$/', (string) $element->getAttribute('style'));
    }
    // The 3-slot workshop of teacher TB is one block of three slot rows.
    $this->drupalGet($this->path($r['plan:A']), ['query' => ['lens' => 'teacher', 'obj' => $r['TB']->id()]]);
    $a3 = '[data-ancora-placement="' . $r['A3']->id() . '"]';
    $session->elementsCount('css', $a3, 1);
    $session->elementTextContains('css', $a3, '11:30–16:00 · 3 slots');
    $this->assertStringContainsString('--r1: 5; --r2: 8;', $this->getSession()->getPage()->find('css', $a3)->getAttribute('style'));

    // The inspector: read-only, with the warning and the pivots.
    $this->drupalGet($this->path($r['plan:A']), ['query' => $query + ['sel' => $r['A1']->id()]]);
    $session->elementTextContains('css', '.ancora-inspector__conflicts', 'in plan Intensive B, period Week B, day 2, slot 2, on 2026-09-15 10:00–10:30');
    // Its only form is the move of #130 (S1b), for those who may move it.
    $session->elementsCount('css', '.ancora-inspector form', 1);
    $session->elementExists('css', '.ancora-inspector form#ancora-move-form');
    $session->linkExists("Room L1's week");
    $session->buttonExists('Find the valid destinations');

    // The undated plan: "not evaluated" everywhere, never "no overlap".
    $this->drupalGet($this->path($r['plan:U']), ['query' => ['sel' => $r['U1']->id()]]);
    $session->elementTextContains('css', '.ancora-wsbar__cross', 'Cross-plan conflicts not evaluated: dates unknown (the plan has no first day).');
    $session->elementsCount('css', '.ancora-block__mark--unknown', 2);
    $session->elementTextContains('css', '.ancora-inspector', 'Dates unknown / cross-plan conflicts not evaluated');
    $session->pageTextNotContains('No overlap found');

    // The regions endpoint: the same regions as a fragment.
    $this->drupalGet($this->path($r['plan:A'], '/regions'), ['query' => ['lens' => 'teacher', 'obj' => $r['TA']->id()]]);
    $session->statusCodeEquals(200);
    $this->assertStringNotContainsString('<html', $this->getSession()->getPage()->getContent());
    $session->elementsCount('css', '[data-ancora-region]', 4);
    $session->elementTextEquals('css', '#ancora-canvas-title', 'Teacher TA: 2 activities');
    $contexts = (string) $this->getSession()->getResponseHeader('X-Drupal-Cache-Contexts');
    $this->assertStringContainsString('user', $contexts);
    $this->assertStringContainsString('url.query_args', $contexts);
    // It records the Resume preference, so it is never cached.
    $this->assertStringStartsWith('0', (string) $this->getSession()->getResponseHeader('X-Drupal-Cache-Max-Age'));

    // The legacy routes keep working, and link to the workspace.
    foreach ([
      ['/ancora/planning/' . $r['plan:A']->id() . '/timetable', ['period' => $r['period:A']->id()]],
      ['/ancora/timetables/explorer', ['period' => $r['period:A']->id(), 'teacher' => $r['TA']->id()]],
      ['/ancora/timetables/placements/' . $r['A1']->id() . '/move', []],
    ] as [$path, $query]) {
      $this->drupalGet($path, ['query' => $query]);
      $session->statusCodeEquals(200);
    }
    $this->drupalGet('/ancora/planning/' . $r['plan:A']->id());
    $session->linkByHrefExists($this->path($r['plan:A']));
  }

  /**
   * Access: the plan timetable's, forged values, two users, the viewer.
   */
  public function testAccess(): void {
    $planner = $this->drupalCreateUser(self::PLANNER);
    $other = $this->drupalCreateUser(self::PLANNER);
    $r = $this->workspaceFixture((int) $planner->id());
    $o = $this->workspaceFixture((int) $other->id(), 'O');
    $viewer = $this->drupalCreateUser(['view timetable viewer']);
    $session = $this->assertSession();
    $query = ['lens' => 'teacher', 'obj' => $r['TA']->id()];

    // Anonymous.
    foreach (['', '/regions'] as $suffix) {
      $this->drupalGet($this->path($r['plan:A'], $suffix));
      $session->statusCodeEquals(403);
    }

    // The viewer: not the editing workspace; its viewer is unchanged.
    $this->drupalLogin($viewer);
    foreach (['', '/regions'] as $suffix) {
      $this->drupalGet($this->path($r['plan:A'], $suffix));
      $session->statusCodeEquals(403);
    }
    $this->drupalGet('/ancora/timetables/viewer');
    $session->statusCodeEquals(200);

    // Two users, the same URL: only the owner gets the content.
    $this->drupalLogin($other);
    $this->drupalGet($this->path($r['plan:A'], '/regions'), ['query' => $query]);
    $session->statusCodeEquals(403);
    $session->pageTextNotContains('Teacher TA:');
    $this->drupalGet($this->path($o['plan:A'], '/regions'), ['query' => ['lens' => 'teacher', 'obj' => $o['TA']->id()]]);
    $session->statusCodeEquals(200);
    $session->elementTextEquals('css', '#ancora-canvas-title', 'Teacher OTA: 2 activities');

    $this->drupalLogin($planner);
    $this->drupalGet($this->path($r['plan:A'], '/regions'), ['query' => $query]);
    $session->statusCodeEquals(200);
    $session->elementTextEquals('css', '#ancora-canvas-title', 'Teacher TA: 2 activities');
    $session->pageTextNotContains('Teacher OTA');
    $this->drupalGet($this->path($o['plan:A']));
    $session->statusCodeEquals(403);

    // Forged or foreign values: 404 on the page and on the regions.
    foreach ([
      ['period' => $o['period:A']->id()],
      ['period' => $r['period:B']->id()],
      ['period' => 'abc'],
      ['lens' => 'resource'],
      ['lens' => 'teacher', 'obj' => $o['TA']->id()],
      ['obj' => $r['TA']->id()],
      ['sel' => $o['A1']->id()],
      ['sel' => $r['U1']->id()],
    ] as $forged) {
      foreach (['', '/regions'] as $suffix) {
        $this->drupalGet($this->path($r['plan:A'], $suffix), ['query' => $forged]);
        $session->statusCodeEquals(404);
      }
    }
  }

  /**
   * The Overview's "Resume <last plan>", revalidated.
   */
  public function testResume(): void {
    $planner = $this->drupalCreateUser(self::PLANNER);
    $other = $this->drupalCreateUser(self::PLANNER);
    $r = $this->workspaceFixture((int) $planner->id());
    $session = $this->assertSession();
    $this->drupalLogin($planner);

    $this->drupalGet('/ancora');
    $session->elementNotExists('css', '[data-ancora-resume]');
    $this->drupalGet($this->path($r['plan:A']), ['query' => ['period' => $r['period:A']->id(), 'lens' => 'teacher']]);
    $this->drupalGet('/ancora');
    $session->elementTextEquals('css', '[data-ancora-resume]', 'Resume Autumn A, Week A');
    $session->linkByHrefExists($this->path($r['plan:A']) . '?period=' . $r['period:A']->id());
    $this->assertSame(
      ['plan' => (int) $r['plan:A']->id(), 'period' => (int) $r['period:A']->id()],
      \Drupal::service('user.data')->get('ancora_scheduling', (int) $planner->id(), ResumeStore::NAME),
    );
    // Only the plan and the period are stored; the Overview is not stale.
    $this->drupalGet($this->path($r['plan:U']));
    $this->drupalGet('/ancora');
    $session->elementTextEquals('css', '[data-ancora-resume]', 'Resume Undated U, Week U');

    // A plan the account may no longer view: no Resume.
    $r['plan:U']->set('uid', $other->id())->save();
    $this->drupalGet('/ancora');
    $session->statusCodeEquals(200);
    $session->elementNotExists('css', '[data-ancora-resume]');
    $this->assertNull(\Drupal::service('user.data')->get('ancora_scheduling', (int) $planner->id(), ResumeStore::NAME));

    // A deleted plan: no Resume either.
    $this->drupalGet($this->path($r['plan:B']));
    $this->drupalGet('/ancora');
    $session->elementTextEquals('css', '[data-ancora-resume]', 'Resume Intensive B, Week B');
    $r['plan:B']->delete();
    $this->drupalGet('/ancora');
    $session->elementNotExists('css', '[data-ancora-resume]');

    // The regions endpoint (the in-place loader) records it too.
    $this->drupalGet($this->path($r['plan:A'], '/regions'), ['query' => ['period' => $r['period:A2']->id()]]);
    $session->statusCodeEquals(200);
    $this->drupalGet('/ancora');
    $session->elementTextEquals('css', '[data-ancora-resume]', 'Resume Autumn A, Week A2');
    // A foreign or forged value is a 404 and records nothing.
    $forgeries = [
      ['period' => $r['period:U']->id()],
      ['period' => 'abc'],
      ['lens' => 'resource'],
      ['sel' => $r['U1']->id()],
    ];
    foreach ($forgeries as $forged) {
      $this->drupalGet($this->path($r['plan:A'], '/regions'), ['query' => $forged]);
      $session->statusCodeEquals(404);
    }
    // Another owner's plan (now plan U): 403, and nothing recorded.
    $this->drupalGet($this->path($r['plan:U'], '/regions'));
    $session->statusCodeEquals(403);
    $this->drupalGet('/ancora');
    $session->elementTextEquals('css', '[data-ancora-resume]', 'Resume Autumn A, Week A2');
  }

}
