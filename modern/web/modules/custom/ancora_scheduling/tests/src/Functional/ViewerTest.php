<?php

declare(strict_types=1);

namespace Drupal\Tests\ancora_scheduling\Functional;

use Drupal\ancora_academic\Export\AcademicExport;
use Drupal\ancora_academic\Fixture\SyntheticFixture;
use Drupal\ancora_engine\Job\JobOptions;
use Drupal\ancora_engine\Job\JobState;
use Drupal\ancora_scheduling\ScopeMapper;
use Drupal\Tests\ancora\Functional\AncoraBrowserTestBase;
use Drupal\user\Entity\Role;
use Drupal\user\RoleInterface;
use PHPUnit\Framework\Attributes\Group;

/**
 * The timetable viewer over HTTP (#121, PAR-092): authenticated only.
 *
 * Workspace mode: the placements are entities, as PlacementPublisher makes
 * them. Kernel TimetableViewerTest covers the lists and refusals in depth;
 * s15 compares the grids and lists with the legacy viewer.
 */
#[Group('ancora_scheduling')]
final class ViewerTest extends AncoraBrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['ancora_scheduling', 'block'];

  /**
   * The permissions of a planner whose role is granted the viewer.
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
   * Alice's plan with two placements and a succeeded run.
   */
  private function alicePlan(): array {
    $alice = $this->drupalCreateUser(self::PLANNER);
    $plan = \Drupal::service(SyntheticFixture::class)->load(TRUE, $alice);
    $etm = \Drupal::entityTypeManager();
    $period = $this->id('ancora_period', 'PER');
    $records = [
      $plan,
      $etm->getStorage('ancora_period')->load($period),
      $etm->getStorage('ancora_specialty')->load($this->id('ancora_specialty', 'E')),
      $etm->getStorage('ancora_level')->load($this->id('ancora_level', 'N')),
    ];
    $jobs = \Drupal::service('ancora_engine.job_repository');
    $jobs->enqueue(ScopeMapper::scope(...$records), new JobOptions(), 60, (int) $alice->id(), NULL, ScopeMapper::source(...$records));
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
        'engine_id' => "v-$i",
        'job' => 1,
      ])->save();
    }
    return [$alice, $plan, $period];
  }

  /**
   * A second planner browses the first one's timetable, read-only.
   */
  public function testAuthenticatedViewer(): void {
    [$alice, $plan, $period] = $this->alicePlan();
    $bob = $this->drupalCreateUser(self::PLANNER);
    $ta = $this->id('ancora_teacher', 'TA');
    $b2 = $this->id('ancora_cohort', 'B2');

    $this->drupalLogin($bob);
    $this->drupalGet('/ancora/timetables');
    $this->clickLink('Timetable viewer');
    $this->assertSession()->addressEquals('/ancora/timetables/viewer');
    $this->assertSession()->pageTextContains('Pick a period, then a cohort, a teacher, a room or an other resource.');
    // Alice's plan and period, the cohorts, teachers and rooms are listed,
    // without account names; Bob has no other resource.
    $this->assertSession()->elementExists('css', 'select#ancora-explorer-period optgroup[label="Plan"] option[value="' . $period . '"]');
    $this->assertSession()->elementExists('css', 'select#ancora-explorer-brigade option[value="' . $b2 . '"]');
    $this->assertSession()->responseNotContains($alice->getAccountName());
    $this->assertSession()->elementsCount('css', 'select#ancora-explorer-resource option', 1);

    $this->drupalGet('/ancora/timetables/viewer', ['query' => ['period' => $period, 'teacher' => $ta]]);
    $this->assertSession()->pageTextContains('Teacher Alpha: 1 activities');
    $cell = $this->assertSession()->elementExists('css', '.ancora-timetable__grid-wrapper td[data-day="1"][data-slot="1"] .ancora-placement');
    foreach (['Lecture', 'Subject Alpha', 'Teacher Alpha', 'Room 1'] as $text) {
      $this->assertStringContainsString($text, $cell->getText());
    }
    // Read-only viewer fields: no editor link, no job, no plan page.
    $this->assertSession()->linkByHrefNotExists('/edit');
    $this->assertSession()->linkByHrefNotExists('/ancora/scheduling/jobs');
    $this->assertSession()->linkByHrefNotExists('/ancora/planning/' . $plan->id());
    $this->assertSession()->responseNotContains('job 1');
    // The plan of Alice stays private elsewhere.
    $this->drupalGet('/ancora/planning/' . $plan->id());
    $this->assertSession()->statusCodeEquals(403);
    $this->drupalGet('/ancora/timetables/explorer', ['query' => ['period' => $period, 'teacher' => $ta]]);
    $this->assertSession()->pageTextContains('That period is not one of yours.');

    // Forged and malformed ids show nothing.
    $forged = [
      ['period' => 999999, 'teacher' => $ta],
      ['period' => 'x', 'teacher' => $ta],
      ['period' => $period, 'teacher' => '1 OR 1=1'],
      ['period' => $period, 'resource' => 1],
    ];
    foreach ($forged as $query) {
      $this->drupalGet('/ancora/timetables/viewer', ['query' => $query]);
      $this->assertSession()->statusCodeEquals(200);
      $this->assertSession()->elementNotExists('css', '.ancora-placement');
    }
    // By cohort; the same URL for Alice is a separate view, cached apart.
    $url = ['query' => ['period' => $period, 'brigade' => $b2]];
    $this->drupalGet('/ancora/timetables/viewer', $url);
    $this->assertSession()->elementExists('css', 'td[data-day="2"][data-slot="3"] .ancora-placement');
    $this->drupalLogin($alice);
    $this->drupalGet('/ancora/timetables/viewer', $url);
    $this->assertSession()->elementsCount('css', 'td[data-day] .ancora-placement', 1);
  }

  /**
   * Anonymous visitors get nothing, whatever they send or are granted.
   */
  public function testAnonymousDenied(): void {
    [, , $period] = $this->alicePlan();
    $ta = $this->id('ancora_teacher', 'TA');
    $attempts = [
      ['period' => $period, 'teacher' => $ta],
      ['period' => $period, 'teacher' => $ta, 'access' => 'public'],
      ['period' => $period, 'teacher' => $ta, 'public_viewer_anonymous' => 'true', 'anonymous' => '1'],
    ];
    foreach ($attempts as $query) {
      $this->drupalGet('/ancora/timetables/viewer', ['query' => $query]);
      $this->assertSession()->statusCodeEquals(403);
      $this->assertSession()->responseNotContains('Subject Alpha');
    }
    $headers = ['X-Ancora-Public' => '1', 'X-Public-Viewer' => 'anonymous'];
    $this->drupalGet('/ancora/timetables/viewer', ['query' => ['period' => $period, 'teacher' => $ta]], $headers);
    $this->assertSession()->statusCodeEquals(403);
    // Even with the permission granted to the anonymous role, the route
    // requires a login.
    Role::load(RoleInterface::ANONYMOUS_ID)->grantPermission('view timetable viewer')->save();
    $this->drupalGet('/ancora/timetables/viewer', ['query' => ['period' => $period, 'teacher' => $ta]]);
    $this->assertSession()->statusCodeEquals(403);
    $this->assertSession()->responseNotContains('Teacher Alpha');
    // No menu link either.
    $this->drupalGet('/user/login');
    $this->assertSession()->linkNotExists('Timetable viewer');
    // A logged-in user without the permission is refused too.
    $this->drupalLogin($this->drupalCreateUser(['view academic data']));
    $this->drupalGet('/ancora/timetables/viewer');
    $this->assertSession()->statusCodeEquals(403);
  }

  /**
   * Least privilege: administrators by default, other roles when granted.
   */
  public function testAccessPolicy(): void {
    // A fresh install grants the viewer to no role (an administrator role, if
    // the profile has one, holds every permission).
    foreach (Role::loadMultiple() as $role) {
      if (!$role->isAdmin()) {
        $this->assertFalse($role->hasPermission('view timetable viewer'), $role->id());
      }
    }
    [, , $period] = $this->alicePlan();
    $ta = $this->id('ancora_teacher', 'TA');
    $url = ['query' => ['period' => $period, 'teacher' => $ta]];
    // "View academic data" (a planner without the grant) is not enough.
    $academic = ['view academic data', 'edit academic data', 'view ancora engine jobs', 'request ancora engine jobs'];
    $planner = $this->drupalCreateUser($academic);
    $this->drupalLogin($planner);
    $this->drupalGet('/ancora/timetables');
    $this->assertSession()->linkNotExists('Timetable viewer');
    $this->drupalGet('/ancora/timetables/viewer', $url);
    $this->assertSession()->statusCodeEquals(403);
    $this->assertSession()->responseNotContains('Subject Alpha');

    // An explicit grant to the planner's role opens it; a revocation closes
    // it again at once (nothing stale is served).
    $rid = $planner->getRoles(TRUE)[0];
    Role::load($rid)->grantPermission('view timetable viewer')->save();
    $this->drupalGet('/ancora/timetables/viewer', $url);
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains('Teacher Alpha: 1 activities');
    Role::load($rid)->revokePermission('view timetable viewer')->save();
    $this->drupalGet('/ancora/timetables/viewer', $url);
    $this->assertSession()->statusCodeEquals(403);
    $this->assertSession()->responseNotContains('Subject Alpha');

    // Administrators see it without the permission: "administer academic
    // data" and user 1.
    $this->drupalLogin($this->drupalCreateUser(['view academic data', 'administer academic data']));
    $this->drupalGet('/ancora/timetables');
    $this->clickLink('Timetable viewer');
    $this->drupalGet('/ancora/timetables/viewer', $url);
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains('Teacher Alpha: 1 activities');
    $this->drupalLogin($this->rootUser);
    $this->drupalGet('/ancora/timetables/viewer', $url);
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains('Teacher Alpha: 1 activities');

    // Anonymous, after all of this, still gets nothing.
    $this->drupalLogout();
    $this->drupalGet('/ancora/timetables/viewer', $url);
    $this->assertSession()->statusCodeEquals(403);
    $this->assertSession()->responseNotContains('Subject Alpha');
  }

  /**
   * The empty state: no plan has a period.
   */
  public function testEmptyState(): void {
    $this->drupalLogin($this->drupalCreateUser(self::PLANNER));
    $this->drupalGet('/ancora/timetables/viewer');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains('There is no period yet.');
  }

}
