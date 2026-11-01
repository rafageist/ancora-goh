<?php

declare(strict_types=1);

namespace Drupal\Tests\ancora_scheduling\Functional;

use Drupal\ancora_academic\Entity\StudentGroup;
use Drupal\ancora_academic\Fixture\SyntheticFixture;
use Drupal\Tests\ancora\Functional\AncoraBrowserTestBase;
use PHPUnit\Framework\Attributes\Group;

/**
 * Cohort membership, cohort and subject forms over HTTP (#70, D6, D10).
 *
 * Workspace mode, so that the placed activities the legacy guard looks at
 * are placement entities of the site.
 */
#[Group('ancora_scheduling')]
final class GroupMembershipUiTest extends AncoraBrowserTestBase {

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
    $this->drupalPlaceBlock('local_tasks_block');
  }

  /**
   * A group of the fixture by code.
   */
  private function group(string $code): StudentGroup {
    $storage = \Drupal::entityTypeManager()->getStorage('ancora_student_group');
    $storage->resetCache();
    $groups = $storage->loadByProperties(['code' => $code]);
    $group = reset($groups);
    assert($group instanceof StudentGroup);
    return $group;
  }

  /**
   * The id of a cohort by code.
   */
  private function cohort(string $code): int {
    $cohorts = \Drupal::entityTypeManager()->getStorage('ancora_cohort')->loadByProperties(['code' => $code]);
    return (int) array_key_first($cohorts);
  }

  /**
   * Moves through the group form, the guard's message, the other forms.
   */
  public function testMembershipEditing(): void {
    $owner = $this->drupalCreateUser(['view academic data', 'edit academic data']);
    $plan = \Drupal::service(SyntheticFixture::class)->load(TRUE, $owner);
    $g1 = $this->group('G1');
    $g2 = $this->group('G2');
    $b1 = $this->cohort('B1');
    $b2 = $this->cohort('B2');

    // A second planner can neither open nor edit the groups.
    $this->drupalLogin($this->drupalCreateUser(['view academic data', 'edit academic data']));
    $this->drupalGet('/admin/ancora/groups/' . $g1->id() . '/edit');
    $this->assertSession()->statusCodeEquals(403);

    $this->drupalLogin($owner);
    // A cohort of another specialty is not offered (the legacy browser
    // shows the cohorts of the group's specialty and level).
    $specialty = \Drupal::entityTypeManager()->getStorage('ancora_specialty')->create([
      'label' => 'Other programme',
      'code' => 'F',
      'uid' => $owner->id(),
    ]);
    $specialty->save();
    $level = $this->group('G1')->get('level')->target_id;
    \Drupal::entityTypeManager()->getStorage('ancora_cohort')->create([
      'label' => 'Cohort F',
      'code' => 'BF',
      'uid' => $owner->id(),
      'specialty' => $specialty->id(),
      'level' => $level,
      'enrollment' => -3,
    ])->save();
    $this->drupalGet('/admin/ancora/groups/' . $g2->id() . '/edit');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->fieldExists("cohorts[$b1]");
    $this->assertSession()->fieldNotExists('cohorts[' . $this->cohort('BF') . ']');
    $this->assertSession()->pageTextContains('checking it here moves it out of its other group');

    // Checking B1 in G2 moves it out of G1.
    $this->submitForm(["cohorts[$b1]" => TRUE], 'Save');
    $this->assertSession()->pageTextContains('Updated the student group');
    $this->assertSame([], $this->group('G1')->getCohortIds());
    $this->assertEqualsCanonicalizing([$b1, $b2], $this->group('G2')->getCohortIds());

    // G2 is placed: it cannot receive a cohort, and B1 cannot leave it for
    // another group of the slot. The message says why.
    $etm = \Drupal::entityTypeManager();
    $etm->getStorage('ancora_placement')->create([
      'plan' => $plan->id(),
      'period' => array_key_first($etm->getStorage('ancora_period')->loadByProperties(['code' => 'PER'])),
      'template' => array_key_first($etm->getStorage('ancora_activity_template')->loadByProperties(['sequence' => 1])),
      'student_group' => $g2->id(),
      'day' => 1,
      'slot' => 1,
      'engine_id' => 'test-1',
      'job' => 1,
    ])->save();
    $this->drupalGet('/admin/ancora/groups/' . $g1->id() . '/edit');
    $this->submitForm(["cohorts[$b1]" => TRUE], 'Save');
    $this->assertSession()->pageTextContains('it would leave the group Group 2, which already has placed activities');
    $this->assertSame([], $this->group('G1')->getCohortIds());

    // Unchecking a cohort of the placed group is not refused (legacy).
    $this->drupalGet('/admin/ancora/groups/' . $g2->id() . '/edit');
    $this->submitForm(["cohorts[$b1]" => FALSE], 'Save');
    $this->assertSession()->pageTextContains('Updated the student group');
    $this->assertSame([$b2], $this->group('G2')->getCohortIds());

    // The cohort form takes any integer enrolment; the subject form needs a
    // specialty, as the legacy subject tree always gives one.
    $this->drupalGet('/admin/ancora/cohorts/' . $b1 . '/edit');
    $this->submitForm(['enrollment[0][value]' => '-7', 'code[0][value]' => 'B 1/x'], 'Save');
    $this->assertSession()->pageTextContains('Updated the cohort');
    $this->drupalGet('/admin/ancora/subjects/add');
    $this->submitForm(['label[0][value]' => 'No specialty', 'code[0][value]' => 'Z'], 'Save');
    $this->assertSession()->pageTextContains('Specialty field is required.');
  }

}
