<?php

declare(strict_types=1);

namespace Drupal\Tests\ancora_scheduling\Functional;

use Drupal\ancora_academic\Entity\Plan;
use Drupal\ancora_academic\Export\AcademicExport;
use Drupal\ancora_academic\Fixture\SyntheticFixture;
use Drupal\ancora_engine\Job\JobOptions;
use Drupal\ancora_engine\Job\JobState;
use Drupal\ancora_scheduling\ScopeMapper;
use Drupal\Tests\ancora\Functional\AncoraBrowserTestBase;
use Drupal\user\UserInterface;
use PHPUnit\Framework\Attributes\Group;

/**
 * The planner workspace (#105) over HTTP: landing, plan, generate, timetable.
 *
 * The site runs the engine in workspace mode, so a finished run's timetable
 * is the placement entities the test creates (as PlacementPublisher would);
 * no engine database is needed. The two-database read is covered by
 * SchedulingPagesTest (unreachable engine) and scripts/r1-journey.sh.
 */
#[Group('ancora_scheduling')]
final class WorkspacePagesTest extends AncoraBrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['ancora_scheduling', 'block'];

  /**
   * The permissions of a planner (scripts/r1-journey.sh).
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
    $settings['settings']['ancora_engine'] = (object) [
      'value' => ['mode' => 'workspace'],
      'required' => TRUE,
    ];
    $this->writeSettings($settings);
    $this->config('system.site')->set('page.front', '/ancora')->save();
    // The blocks of config/sync that the workspace relies on.
    $menu = ['level' => 1, 'depth' => 1];
    $this->drupalPlaceBlock('system_menu_block:main', ['id' => 'main_nav', 'region' => 'header'] + $menu);
    $section = ['level' => 2, 'depth' => 1, 'weight' => -40];
    $this->drupalPlaceBlock('system_menu_block:main', ['id' => 'section_nav', 'region' => 'content'] + $section);
    $this->drupalPlaceBlock('system_menu_block:account', ['id' => 'account_nav', 'region' => 'header']);
    $this->drupalPlaceBlock('system_breadcrumb_block', ['region' => 'highlighted']);
    $this->drupalPlaceBlock('page_title_block', ['region' => 'content', 'weight' => -30]);
  }

  /**
   * The owning planner: landing → plan → generate/status → timetable.
   */
  public function testPlannerJourney(): void {
    $planner = $this->drupalCreateUser(self::PLANNER);
    $plan = \Drupal::service(SyntheticFixture::class)->load(TRUE, $planner);
    $plan_id = (int) $plan->id();

    // Anonymous visitors get the way to log in, nothing else.
    $this->drupalGet('');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains('Log in with your planner account');
    $this->assertSession()->linkByHrefExists('/user/login');
    $this->assertSession()->pageTextNotContains('Teacher Alpha');
    $restricted = [
      "/ancora/planning/$plan_id",
      "/ancora/planning/$plan_id/timetable",
      '/ancora/timetables',
      '/ancora/scheduling/jobs',
      '/ancora/planning/catalogues',
    ];
    foreach ($restricted as $path) {
      $this->drupalGet($path);
      $this->assertSession()->statusCodeEquals(403);
    }

    // Logging in arrives at the overview.
    $this->drupalGet('/user/login');
    $this->submitForm(['name' => $planner->getAccountName(), 'pass' => $planner->passRaw], 'Log in');
    $this->assertSession()->addressEquals('/ancora');
    $this->assertSession()->elementTextEquals('css', 'h1', 'Overview');
    $this->assertAccessibleBasics();

    // The primary navigation and the demo entry links (#104 item 5).
    $navigation = [
      'Overview',
      'Plans & periods',
      'Academic catalogues',
      'Assignments & templates',
      'Generate',
      'Timetables',
    ];
    foreach ($navigation as $title) {
      $this->assertSession()->elementExists('xpath', "//header//nav//a[normalize-space()='$title']");
    }
    $quick_links = [
      'Planning',
      'Plans',
      'Periods',
      'Teachers',
      'Rooms',
      'Subjects',
      'Groups',
      'Generate timetable',
      'View timetable',
      'Job status',
    ];
    foreach ($quick_links as $title) {
      $this->assertSession()->elementExists('xpath', "//ul[contains(@class, 'ancora-quick-links')]//a[normalize-space()='$title']");
    }
    // The plan row: periods, groups and templates of the plan.
    $row = $this->assertSession()->elementExists('xpath', "//table[contains(@class, 'ancora-workspace__plans')]//tr[td/a[normalize-space()='Plan']]");
    $cells = array_map(static fn ($cell): string => trim($cell->getText()), $row->findAll('css', 'td'));
    $this->assertSame(['Plan', 'P', 'Yes', '1', '2', '4'], array_slice($cells, 0, 6));
    $this->assertSession()->elementTextContains('css', '.ancora-counts', '2 Teachers');
    $this->assertSession()->elementTextContains('css', '.ancora-counts', '2 Subjects');
    $this->assertSession()->pageTextContains('No timetable has been requested yet.');

    // A section page of the navigation, with its lists.
    $this->clickLink('Academic catalogues');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->elementTextEquals('css', 'h1', 'Academic catalogues');
    $this->assertSession()->elementsCount('css', 'li.ancora-planning__step', 8);
    $this->assertSession()->elementExists('xpath', "//main//nav//a[normalize-space()='Teachers']");
    $this->clickLink('Teachers');
    $this->assertSession()->elementTextContains('css', 'table', 'Teacher Alpha');
    $this->assertSession()->elementExists('xpath', "//main//nav//a[contains(@class, 'is-active-trail') and normalize-space()='Teachers']");

    // Click 1: the plan page.
    $this->drupalGet('');
    $this->getSession()->getPage()->find('xpath', "//table//a[normalize-space()='Plan']")->click();
    $this->assertSession()->addressEquals("/ancora/planning/$plan_id");
    $this->assertSession()->elementTextEquals('css', 'h1', 'Plan');
    $this->assertSession()->elementExists('xpath', "//nav[@aria-labelledby='system-breadcrumb']//a[normalize-space()='Planning']");
    $this->assertSession()->elementTextContains('css', 'table', 'Week');
    $this->assertSession()->linkExists('Edit plan');
    $this->assertSession()->linkExists('View timetable');
    $this->assertSession()->linkByHrefExists("/admin/ancora/periods/add?plan=$plan_id");
    $this->assertSession()->linkByHrefExists("/admin/ancora/templates?plan=$plan_id");
    $records = $this->getSession()->getPage()->find('xpath', "//ul[contains(@class, 'ancora-records')]/li[span[normalize-space()='Activity templates']]");
    $this->assertNotNull($records);
    $this->assertStringContainsString('4', $records->find('css', '.ancora-records__count')->getText());
    $this->assertAccessibleBasics();

    // A new record from the plan page is pre-filled with the plan and comes
    // back to it.
    $this->clickLink('Add period');
    $this->assertSession()->fieldValueEquals('plan', (string) $plan_id);
    $this->submitForm([
      'label[0][value]' => 'Second week',
      'code[0][value]' => 'PER2',
      'days[0][value]' => 5,
      'slots_per_day[0][value]' => 4,
      'start_hour[0][value]' => 8,
      'slot_duration[0][value]' => 1,
    ], 'Save');
    $this->assertSession()->addressEquals("/ancora/planning/$plan_id");
    $this->assertSession()->pageTextContains('Created the period Second week.');

    // Click 2: generate. The form only queues the run.
    $this->clickLink('Request a timetable');
    $this->assertSession()->fieldValueEquals('plan', (string) $plan_id);
    $one = fn (string $type, array $properties) => current(\Drupal::entityTypeManager()->getStorage($type)->loadByProperties($properties));
    $period = $one('ancora_period', ['plan' => $plan_id, 'code' => 'PER']);
    $this->submitForm([
      'plan' => $plan_id,
      'period' => $period->id(),
      'specialty' => $one('ancora_specialty', ['uid' => $planner->id(), 'code' => 'E'])->id(),
      'level' => $one('ancora_level', ['uid' => $planner->id(), 'code' => 'N'])->id(),
    ], 'Request the timetable');
    $this->assertSession()->addressEquals('/ancora/scheduling/jobs/1');
    $this->assertSession()->pageTextContains('Queued');
    $this->assertSession()->linkExists('Plan page');
    $this->drupalGet('/ancora/scheduling/jobs');
    $this->assertSession()->linkExists('Run 1');
    $this->assertSession()->pageTextContains('Queued');

    // The worker's outcome: a succeeded run and its placements.
    $this->finishWithPlacements($plan, $planner);
    $this->drupalGet('/ancora/scheduling/jobs/1');
    $this->assertSession()->pageTextContains('Succeeded: final timetable');

    // Landing → View timetable: the timetable in two clicks.
    $this->drupalGet('');
    $this->clickLink('View timetable');
    $this->assertSession()->addressEquals("/ancora/planning/$plan_id/timetable");
    $this->assertSession()->elementTextEquals('css', 'h1', 'Timetable: Plan');
    $this->assertSession()->elementExists('css', '[data-timetable="final"]');
    $this->assertSession()->elementNotExists('css', '[data-timetable="draft"]');
    $this->assertSession()->pageTextContains('Final timetable, generated by job 1.');
    $this->assertSession()->pageTextNotContains('The planning data changed after this run');
    $this->assertSession()->elementTextContains('css', 'thead', 'Day 1');
    $this->assertSession()->elementTextContains('css', 'tbody th', '08:00–09:00');
    $this->assertSession()->elementsCount('css', 'td[data-day] .ancora-placement', 2);
    $cell = $this->assertSession()->elementExists('css', 'td[data-day="1"][data-slot="1"] .ancora-placement');
    $this->assertSame('G1', $cell->getAttribute('data-group'));
    $this->assertStringContainsString('Teacher Alpha', $cell->getText());
    $this->assertStringContainsString('Room 1', $cell->getText());
    // The narrow-screen list holds the same placements, by day.
    $this->assertSession()->elementsCount('css', '.ancora-timetable__item', 2);
    $this->assertSession()->elementTextContains('css', '.ancora-timetable__list', 'Day 2');
    $this->assertAccessibleBasics();
    foreach (['group', 'teacher', 'room'] as $key) {
      $this->assertSession()->elementExists('css', "label[for='ancora-timetable-filter-$key']");
      $this->assertSession()->elementExists('css', "select#ancora-timetable-filter-$key");
    }

    // Server-side filters (query parameters); unknown values are ignored.
    $this->submitForm(['group' => 'G1'], 'Filter');
    $this->assertSession()->elementsCount('css', 'td[data-day] .ancora-placement', 1);
    $this->assertSession()->pageTextContains('1 of 2 placements match the filters.');
    $this->clickLink('Show all placements');
    $this->assertSession()->elementsCount('css', 'td[data-day] .ancora-placement', 2);
    $this->drupalGet("/ancora/planning/$plan_id/timetable", ['query' => ['period' => $period->id(), 'room' => 'L2']]);
    $this->assertSession()->elementsCount('css', 'td[data-day] .ancora-placement', 1);
    $this->assertSession()->elementTextContains('css', 'td[data-day] .ancora-placement', 'Room 2');
    $none = ['period' => $period->id(), 'teacher' => 'TA', 'room' => 'L2'];
    $this->drupalGet("/ancora/planning/$plan_id/timetable", ['query' => $none]);
    $this->assertSession()->pageTextContains('No placement matches the filters.');
    $unknown = ['period' => $period->id(), 'group' => 'NOPE'];
    $this->drupalGet("/ancora/planning/$plan_id/timetable", ['query' => $unknown]);
    $this->assertSession()->elementsCount('css', 'td[data-day] .ancora-placement', 2);

    // The other period has no run yet; the timetables page lists both.
    $this->drupalGet('/ancora/timetables');
    $this->assertSession()->pageTextContains('Succeeded: final timetable');
    $this->assertSession()->pageTextContains('No run yet');
    $this->assertSession()->elementsCount('xpath', "//main//a[normalize-space()='View timetable']", 2);
  }

  /**
   * A second planner sees none of the first planner's restricted records.
   */
  public function testSecondPlannerSeesNothing(): void {
    $planner = $this->drupalCreateUser(self::PLANNER);
    $plan = \Drupal::service(SyntheticFixture::class)->load(TRUE, $planner);
    $plan_id = (int) $plan->id();
    $this->finishWithPlacements($plan, $planner, TRUE);
    $teacher = current(\Drupal::entityTypeManager()->getStorage('ancora_teacher')->loadByProperties(['code' => 'TA']));

    $other = $this->drupalCreateUser(self::PLANNER);
    $this->drupalLogin($other);
    $this->drupalGet('');
    // The zero-data getting-started path.
    $this->assertSession()->pageTextContains('You have no plan yet.');
    $this->assertSession()->pageTextContains('Getting started');
    $this->assertSession()->linkExists('Create a plan');
    $this->assertSession()->elementNotExists('css', 'table.ancora-workspace__plans');
    $this->assertSession()->elementTextContains('css', '.ancora-counts', '0 Teachers');
    $this->assertSession()->pageTextContains('No timetable has been requested yet.');
    $this->assertSession()->linkNotExists('Run 1');
    $this->assertSession()->linkByHrefNotExists("/ancora/planning/$plan_id");
    $this->assertNoFixtureText();
    $this->assertAccessibleBasics();

    // Pages and URLs of the first planner's records.
    $denied = [
      "/ancora/planning/$plan_id",
      "/ancora/planning/$plan_id/timetable",
      '/ancora/scheduling/jobs/1',
      "/admin/ancora/plans/$plan_id",
    ];
    foreach ($denied as $path) {
      $this->drupalGet($path);
      $this->assertSession()->statusCodeEquals(403);
      $this->assertNoFixtureText();
    }
    // The own-records pages, also with the other plan's id in the URL.
    $own = [
      '/ancora/timetables',
      '/ancora/scheduling/jobs',
      '/ancora/planning',
      '/ancora/planning/catalogues',
      '/ancora/planning/plans-periods',
      '/ancora/planning/assignments-templates',
      '/admin/ancora/teachers',
      '/admin/ancora/templates',
      '/ancora/scheduling/request',
    ];
    foreach ($own as $path) {
      $this->drupalGet($path, ['query' => ['plan' => $plan_id]]);
      $this->assertSession()->statusCodeEquals(200);
      $this->assertNoFixtureText();
      $this->assertSession()->linkNotExists('Run 1');
    }
    // Every count of a section page is zero.
    $this->drupalGet('/ancora/planning');
    foreach ($this->getSession()->getPage()->findAll('css', '.ancora-planning__count') as $count) {
      $this->assertSame('(0)', $count->getText());
    }
    // A teacher (or room) itself stays visible to every viewer (Q-23).
    $this->drupalGet('/admin/ancora/teachers/' . $teacher->id());
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains('Teacher Alpha');

    // The create action of the zero state.
    $this->drupalGet('');
    $this->clickLink('Create a plan');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->fieldExists('label[0][value]');

    // A viewer who may not create plans is told so; a user without the
    // planning permissions gets an explanation, not an empty page.
    $this->drupalLogin($this->drupalCreateUser(['view academic data']));
    $this->drupalGet('');
    $this->assertSession()->pageTextContains('may view planning data but not create it');
    $this->assertSession()->linkNotExists('Create a plan');
    $this->assertNoFixtureText();
    $this->drupalLogin($this->drupalCreateUser([]));
    $this->drupalGet('');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains('Your account has no access to the planning data');
  }

  /**
   * Finishes job 1 (queueing it when asked) with two placements.
   */
  private function finishWithPlacements(Plan $plan, UserInterface $owner, bool $enqueue = FALSE): void {
    $storage = fn (string $type) => \Drupal::entityTypeManager()->getStorage($type);
    $one = fn (string $type, array $properties) => current($storage($type)->loadByProperties($properties));
    $period = $one('ancora_period', ['plan' => $plan->id(), 'code' => 'PER']);
    $jobs = \Drupal::service('ancora_engine.job_repository');
    if ($enqueue) {
      $specialty = $one('ancora_specialty', ['uid' => $owner->id(), 'code' => 'E']);
      $level = $one('ancora_level', ['uid' => $owner->id(), 'code' => 'N']);
      $jobs->enqueue(
        ScopeMapper::scope($plan, $period, $specialty, $level),
        new JobOptions(),
        60,
        (int) $owner->id(),
        NULL,
        ScopeMapper::source($plan, $period, $specialty, $level),
      );
    }
    \Drupal::service('entity.memory_cache')->deleteAll();
    $hash = ScopeMapper::hash(\Drupal::service(AcademicExport::class)->export($plan));
    $jobs->finish($jobs->claim('test'), JobState::Succeeded, ['kept' => 'placements', 'input_hash' => $hash]);

    $templates = $storage('ancora_activity_template')->loadByProperties(['plan' => $plan->id()]);
    ksort($templates);
    $templates = array_values($templates);
    $rows = [
      [$templates[0], 'G1', 'TA', 'L1', 1, 1],
      [$templates[2], 'G2', 'TB', 'L2', 2, 3],
    ];
    foreach ($rows as $i => [$template, $group, $teacher, $room, $day, $slot]) {
      $storage('ancora_placement')->create([
        'plan' => $plan->id(),
        'period' => $period->id(),
        'template' => $template->id(),
        'student_group' => $one('ancora_student_group', ['plan' => $plan->id(), 'code' => $group])->id(),
        'teacher' => $one('ancora_teacher', ['uid' => $owner->id(), 'code' => $teacher])->id(),
        'room' => $one('ancora_room', ['uid' => $owner->id(), 'code' => $room])->id(),
        'day' => $day,
        'slot' => $slot,
        'engine_id' => "test-$i",
        'job' => 1,
      ])->save();
    }
  }

  /**
   * Asserts that no restricted record of the fixture shows on the page.
   */
  private function assertNoFixtureText(): void {
    foreach (['Teacher Alpha', 'Teacher Beta', 'Room 1', 'Subject Beta', 'Group 1', 'Cohort 1'] as $text) {
      $this->assertSession()->pageTextNotContains($text);
    }
  }

  /**
   * One h1, a skip link, a main landmark and no link without a name.
   */
  private function assertAccessibleBasics(): void {
    $this->assertSession()->elementsCount('css', 'h1', 1);
    $this->assertSession()->elementExists('css', 'a[href="#main-content"]');
    $this->assertSession()->elementExists('css', 'main');
    $this->assertSession()->elementsCount('xpath', "//main//a[not(@href) and not(@id='main-content')]", 0);
    $unnamed = $this->getSession()->getPage()->findAll('xpath', "//a[normalize-space()='' and not(@aria-label) and not(.//img[@alt])]");
    $unnamed = array_filter($unnamed, static fn ($link): bool => $link->getAttribute('id') !== 'main-content');
    $this->assertSame([], array_map(static fn ($link) => $link->getOuterHtml(), $unnamed), 'every link has an accessible name');
  }

}
