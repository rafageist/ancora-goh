<?php

declare(strict_types=1);

namespace Drupal\Tests\ancora_scheduling\Functional;

use Drupal\ancora_academic\Entity\Plan;
use Drupal\ancora_academic\Export\AcademicExport;
use Drupal\ancora_academic\Fixture\SyntheticFixture;
use Drupal\ancora_engine\Job\Job;
use Drupal\ancora_engine\Job\JobState;
use Drupal\ancora_engine\JobRepository;
use Drupal\ancora_scheduling\ScopeMapper;
use Drupal\Core\Url;
use Drupal\Tests\ancora\Functional\AncoraBrowserTestBase;
use PHPUnit\Framework\Attributes\Group;

/**
 * Phase 3 slice A (FC-29, FC-31): generate from the context, follow the run.
 *
 * The request form takes its scope from the query, but only an id it
 * already offers to the user; the plan page and the timetable link to it
 * with the scope; the run page offers "Refresh" (and a bounded reload
 * script) while the run is queued or running, and the workspace of the
 * run's period. Nothing reaches the engine differently: a prefilled request
 * and a manual one queue the same scope, source, options and input.
 */
#[Group('ancora_scheduling')]
final class RequestPrefillTest extends AncoraBrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['ancora_scheduling'];

  /**
   * The planner's records: plan, period, specialty, level, and extras.
   *
   * @var array<string, \Drupal\ancora_academic\AcademicEntityInterface>
   */
  private array $r = [];

  /**
   * The other planner's records.
   *
   * @var array<string, \Drupal\ancora_academic\AcademicEntityInterface>
   */
  private array $foreign = [];

  /**
   * The job repository.
   */
  private function jobs(): JobRepository {
    return $this->container->get('ancora_engine.job_repository');
  }

  /**
   * One record by properties.
   */
  private function one(string $type, array $properties): object {
    return current(\Drupal::entityTypeManager()->getStorage($type)->loadByProperties($properties));
  }

  /**
   * Creates and saves a record.
   */
  private function create(string $type, array $values): object {
    $record = \Drupal::entityTypeManager()->getStorage($type)->create($values);
    $record->save();
    return $record;
  }

  /**
   * The request form with a query.
   */
  private function request(array $query = []): Url {
    return Url::fromRoute('ancora_scheduling.request', [], ['query' => $query]);
  }

  /**
   * The href of the request form with a query.
   */
  private function href(array $query): string {
    return $this->request($query)->toString();
  }

  /**
   * The ids of the planner's scope.
   */
  private function scope(): array {
    return [
      'plan' => $this->r['plan']->id(),
      'period' => $this->r['period']->id(),
      'specialty' => $this->r['specialty']->id(),
      'level' => $this->r['level']->id(),
    ];
  }

  /**
   * Asserts the period, specialty and level fields are not prefilled.
   */
  private function assertNotPrefilled(): void {
    foreach (['period', 'specialty', 'level'] as $key) {
      $this->assertSession()->fieldValueEquals($key, '');
    }
  }

  /**
   * Asserts the legacy option defaults of the form are unchanged.
   */
  private function assertOptionDefaults(): void {
    $this->assertSession()->checkboxChecked('edit-room-choice-nearest');
    $this->assertSession()->checkboxChecked('edit-forma-1');
    $this->assertSession()->fieldValueEquals('compactar', '1');
  }

  /**
   * Finishes the oldest queued job.
   */
  private function finishNext(JobState $state = JobState::Succeeded): Job {
    $job = $this->jobs()->claim('test');
    $this->jobs()->finish($job, $state, ['kept' => 'placements']);
    return $this->jobs()->load($job->id);
  }

  /**
   * What the worker would read for a job: its input snapshot hash.
   *
   * EngineSync::prepare() and WorkspaceSync::snapshot() both hash the export
   * of the job source's plan (ScopeMapper::hash()).
   */
  private function inputHash(Job $job): string {
    \Drupal::entityTypeManager()->getStorage('ancora_plan')->resetCache();
    $plan = Plan::load($job->source->planId);
    return ScopeMapper::hash(\Drupal::service(AcademicExport::class)->export($plan));
  }

  /**
   * Asserts two jobs reach the engine identically.
   */
  private function assertSameInput(Job $a, Job $b): void {
    $this->assertSame($a->scope->toParameters(), $b->scope->toParameters(), 'the same JobScope');
    $this->assertEquals($a->source, $b->source, 'the same JobSource');
    $this->assertEquals($a->options, $b->options, 'the same options');
    $this->assertSame($a->timeLimit, $b->timeLimit, 'the same time limit');
    $this->assertSame($this->inputHash($a), $this->inputHash($b), 'the same input snapshot hash');
  }

  /**
   * Prefill, forged ids, links, the run page and the MPI inputs.
   */
  public function testGenerateFromTheContext(): void {
    $planner = $this->drupalCreateUser([
      'view academic data',
      'edit academic data',
      'view ancora engine jobs',
      'request ancora engine jobs',
    ]);
    $plan = \Drupal::service(SyntheticFixture::class)->load(TRUE, $planner);
    $this->r = [
      'plan' => $plan,
      'period' => $this->one('ancora_period', ['plan' => $plan->id(), 'code' => 'PER']),
      'specialty' => $this->one('ancora_specialty', ['uid' => $planner->id(), 'code' => 'E']),
      'level' => $this->one('ancora_level', ['uid' => $planner->id(), 'code' => 'N']),
    ];
    // More of the planner's own records, so a prefill is not the only
    // choice: a second period of the plan, a second specialty and level.
    $in = ['uid' => $planner->id()];
    $this->r['period2'] = $this->create('ancora_period', [
      'plan' => $plan->id(),
      'label' => 'Second week',
      'code' => 'PER2',
      'plan_days' => [1],
    ]);
    $this->r['specialty2'] = $this->create('ancora_specialty', $in + ['label' => 'Another programme', 'code' => 'E2']);
    $this->r['level2'] = $this->create('ancora_level', $in + ['label' => 'Another year', 'code' => 'N2']);

    // Another planner with records of their own.
    $other = $this->drupalCreateUser([
      'view academic data',
      'edit academic data',
      'view ancora engine jobs',
      'request ancora engine jobs',
    ]);
    $o = ['uid' => $other->id()];
    $this->foreign['plan'] = $this->create('ancora_plan', $o + ['label' => 'Foreign plan', 'code' => 'FP']);
    $this->foreign['period'] = $this->create('ancora_period', [
      'plan' => $this->foreign['plan']->id(),
      'label' => 'Foreign week',
      'code' => 'FW',
      'plan_days' => [1],
    ]);
    $this->foreign['specialty'] = $this->create('ancora_specialty', $o + ['label' => 'Foreign course', 'code' => 'FE']);
    $this->foreign['level'] = $this->create('ancora_level', $o + ['label' => 'Foreign year', 'code' => 'FN']);

    $this->drupalLogin($planner);

    // Without a query: the form as it was (nothing prefilled, the legacy
    // option defaults).
    $this->drupalGet($this->request());
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->fieldValueEquals('plan', (string) $plan->id());
    $this->assertNotPrefilled();
    $this->assertOptionDefaults();
    // Another planner's plan and records are not offered.
    foreach (['plan', 'period', 'specialty', 'level'] as $key) {
      $this->assertSession()->optionNotExists($key, (string) $this->foreign[$key]->id());
    }

    // The plan page: "Generate" on each period row, with the plan and the
    // period.
    $this->drupalGet('/ancora/planning/' . $plan->id());
    foreach (['period', 'period2'] as $key) {
      $this->assertSession()->linkByHrefExists($this->href(['plan' => $plan->id(), 'period' => $this->r[$key]->id()]));
    }
    $this->assertSession()->linkExists('Generate');
    $this->clickLink('Generate');
    $this->assertSession()->addressEquals($this->href(['plan' => $plan->id(), 'period' => $this->r['period']->id()]));
    $this->assertSession()->fieldValueEquals('plan', (string) $plan->id());
    $this->assertSession()->fieldValueEquals('period', (string) $this->r['period']->id());
    $this->assertSession()->fieldValueEquals('specialty', '');
    $this->assertSession()->fieldValueEquals('level', '');
    $this->assertOptionDefaults();

    // The whole scope from the query.
    $this->drupalGet($this->request($this->scope()));
    foreach ($this->scope() as $key => $id) {
      $this->assertSession()->fieldValueEquals($key, (string) $id);
    }
    $this->assertOptionDefaults();
    // The form keeps its CSRF token.
    $this->assertSession()->hiddenFieldExists('form_token');

    // Forged, foreign and malformed ids are ignored silently: the plain
    // form, no error, nothing of the other planner's records in the page.
    $forged = [
      array_map(static fn ($record): int => (int) $record->id(), $this->foreign),
      ['period' => 999999, 'specialty' => 999999, 'level' => 999999],
      [
        'period' => $this->r['period']->id() . 'x',
        'specialty' => ' ' . $this->r['specialty']->id(),
        'level' => '-' . $this->r['level']->id(),
      ],
      ['period' => '0', 'specialty' => '', 'level' => $this->r['level']->id() . '.0'],
    ];
    foreach ($forged as $query) {
      $this->drupalGet($this->request($query));
      $this->assertSession()->statusCodeEquals(200);
      $this->assertSession()->fieldValueEquals('plan', (string) $plan->id());
      $this->assertNotPrefilled();
      $this->assertSession()->pageTextNotContains('Foreign');
      $this->assertSession()->elementNotExists('css', '[role="alert"], .messages--error');
    }
    // Arrays in the query are ignored too (no 400).
    $this->drupalGet($this->request([
      'plan' => [$plan->id()],
      'period' => [$this->r['period']->id()],
      'specialty' => ['x' => $this->r['specialty']->id()],
    ]));
    $this->assertStringContainsString('period%5B0%5D=', $this->getUrl());
    $this->assertSession()->statusCodeEquals(200);
    $this->assertNotPrefilled();

    // A tampered CSRF token is refused as before: nothing is queued.
    $this->drupalGet($this->request($this->scope()));
    $this->getSession()->getPage()->find('css', 'input[name="form_token"]')->setValue('forged');
    $this->submitForm([], 'Request the timetable');
    $this->assertSession()->pageTextContains('The form has become outdated.');
    $this->assertSame([], $this->jobs()->recent());

    // A prefilled request, submitted as offered.
    $this->drupalGet($this->request($this->scope()));
    $this->submitForm([], 'Request the timetable');
    $this->assertSession()->addressEquals('/ancora/scheduling/jobs/1');
    $prefilled = $this->jobs()->load(1);
    $this->assertSame(JobState::Queued, $prefilled->state);
    // The legacy defaults (D9c, D11): buscalugar TRUE, forma 1, compactar 1.
    $this->assertTrue($prefilled->options->buscalugar);
    $this->assertSame([1, 1], [$prefilled->options->forma, $prefilled->options->compactar]);

    // The run page while queued: "Refresh" and the reload script, and the
    // workspace of the run's period.
    $workspace = Url::fromRoute('ancora_scheduling.plan_workspace', ['ancora_plan' => $plan->id()], ['query' => ['period' => $this->r['period']->id()]])->toString();
    $this->assertSession()->pageTextContains('Queued');
    $this->assertSession()->linkByHrefExists('/ancora/scheduling/jobs/1');
    $this->assertSession()->elementExists('css', '[data-ancora-job-pending="1"][role="status"] a.button');
    $this->assertSession()->responseContains('js/job-refresh.js');
    $this->assertSession()->linkByHrefExists($workspace);
    // Without JavaScript, "Refresh" reloads the page.
    $this->clickLink('Refresh');
    $this->assertSession()->addressEquals('/ancora/scheduling/jobs/1');
    $this->assertSession()->pageTextContains('Queued');

    // The timetable while the run is queued: no "Run this scope again".
    $timetable = '/ancora/planning/' . $plan->id() . '/timetable';
    $this->drupalGet($timetable, ['query' => ['period' => $this->r['period']->id()]]);
    $this->assertSession()->pageTextContains('Job 1 is queued.');
    $this->assertSession()->linkNotExists('Run this scope again');

    // Running: still "Refresh" and the script.
    $running = $this->jobs()->claim('test');
    $this->assertSame(1, $running->id);
    $this->drupalGet('/ancora/scheduling/jobs/1');
    $this->assertSession()->pageTextContains('Running');
    $this->assertSession()->elementExists('css', '[data-ancora-job-pending="1"]');
    $this->assertSession()->linkExists('Refresh');
    $this->assertSession()->responseContains('js/job-refresh.js');
    $this->drupalGet($timetable, ['query' => ['period' => $this->r['period']->id()]]);
    $this->assertSession()->linkNotExists('Run this scope again');

    // Ended: neither "Refresh" nor the script; the workspace link stays.
    $this->jobs()->finish($running, JobState::Succeeded, ['kept' => 'placements']);
    $this->drupalGet('/ancora/scheduling/jobs/1');
    $this->assertSession()->pageTextContains('Succeeded: final timetable');
    $this->assertSession()->elementNotExists('css', '[data-ancora-job-pending]');
    $this->assertSession()->linkNotExists('Refresh');
    $this->assertSession()->responseNotContains('js/job-refresh.js');
    $this->assertSession()->linkByHrefExists($workspace);
    $this->assertSession()->linkExists('Open in the workspace');

    // A manual request of the same scope: the plain form, chosen by hand.
    $this->drupalGet($this->request());
    $this->assertNotPrefilled();
    $this->submitForm($this->scope(), 'Request the timetable');
    $this->assertSession()->addressEquals('/ancora/scheduling/jobs/2');
    $manual = $this->jobs()->load(2);
    $this->assertSameInput($prefilled, $manual);
    $this->finishNext();

    // The timetable: "Run this scope again" with the scope of its run.
    $this->drupalGet($timetable, ['query' => ['period' => $this->r['period']->id()]]);
    $this->assertSession()->linkByHrefExists($this->href($this->scope()));
    $this->clickLink('Run this scope again');
    foreach ($this->scope() as $key => $id) {
      $this->assertSession()->fieldValueEquals($key, (string) $id);
    }
    $this->assertOptionDefaults();
    $this->submitForm([], 'Request the timetable');
    $this->assertSession()->addressEquals('/ancora/scheduling/jobs/3');
    $again = $this->jobs()->load(3);
    $this->assertSameInput($manual, $again);
    // Every run of the slice: the legacy defaults, unless changed.
    foreach ([$prefilled, $manual, $again] as $job) {
      $this->assertSame([TRUE, 1, 1], [$job->options->buscalugar, $job->options->forma, $job->options->compactar]);
    }

    // A prefilled form whose options the planner changes posts the change,
    // as the plain form does.
    $this->finishNext(JobState::Cancelled);
    $this->drupalGet($this->request($this->scope()));
    $this->submitForm(['forma' => '2', 'compactar' => '3', 'room_choice' => 'most_used'], 'Request the timetable');
    $changed = $this->jobs()->load(4);
    $options = $changed->options;
    $this->assertSame([FALSE, 2, 3], [$options->buscalugar, $options->forma, $options->compactar]);
    $this->assertSame($prefilled->scope->toParameters(), $changed->scope->toParameters());

    // The other planner: nothing of the planner's plan is offered, a forged
    // prefill falls back silently, and the run page stays refused.
    $this->drupalLogin($other);
    $this->drupalGet($this->request($this->scope()));
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->fieldValueEquals('plan', (string) $this->foreign['plan']->id());
    $this->assertNotPrefilled();
    foreach (['plan', 'period', 'specialty', 'level', 'period2', 'specialty2', 'level2'] as $key) {
      $this->assertSession()->optionNotExists(preg_replace('/2$/', '', $key), (string) $this->r[$key]->id());
    }
    $this->drupalGet('/ancora/scheduling/jobs/1');
    $this->assertSession()->statusCodeEquals(403);
    // Their own scope is prefilled.
    $this->drupalGet($this->request(array_map(static fn ($record): int => (int) $record->id(), $this->foreign)));
    foreach (['period', 'specialty', 'level'] as $key) {
      $this->assertSession()->fieldValueEquals($key, (string) $this->foreign[$key]->id());
    }

    // An account that may see the plan but not request runs: no
    // "Generate", no "Run this scope again", and the request form is
    // refused; the run page still links the workspace.
    $admin = $this->drupalCreateUser(['view academic data', 'administer academic data', 'view ancora engine jobs']);
    $this->drupalLogin($admin);
    $this->drupalGet('/ancora/planning/' . $plan->id());
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->linkNotExists('Generate');
    $this->drupalGet($timetable, ['query' => ['period' => $this->r['period']->id()]]);
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->linkNotExists('Run this scope again');
    $this->drupalGet($this->request($this->scope()));
    $this->assertSession()->statusCodeEquals(403);
    $this->drupalGet('/ancora/scheduling/jobs/1');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->linkByHrefExists($workspace);
  }

}
