<?php

declare(strict_types=1);

namespace Drupal\Tests\ancora_academic\Functional;

use Drupal\ancora_academic\Fixture\SyntheticFixture;
use Drupal\Tests\ancora\Functional\AncoraBrowserTestBase;
use PHPUnit\Framework\Attributes\Group;

/**
 * The planning UI through HTTP: overview, lists, forms and permissions.
 */
#[Group('ancora')]
final class PlanningUiTest extends AncoraBrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['ancora_academic', 'block'];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->drupalPlaceBlock('local_actions_block');
    $this->drupalPlaceBlock('local_tasks_block');
  }

  /**
   * Anonymous users are denied everything; viewers only read.
   */
  public function testAnonymousAndViewer(): void {
    \Drupal::service(SyntheticFixture::class)->load();
    $paths = [
      '/ancora/planning',
      '/ancora/planning/activities',
      '/admin/ancora/teachers',
      '/admin/ancora/teachers/add',
      '/admin/ancora/plans/1',
    ];
    foreach ($paths as $path) {
      $this->drupalGet($path);
      $this->assertSession()->statusCodeEquals(403);
    }

    $this->drupalLogin($this->drupalCreateUser(['view academic data']));
    $this->drupalGet('/ancora/planning');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->elementsCount('css', 'li.ancora-planning__step', 13);
    $this->assertSession()->linkExists('Teacher assignments');
    $this->assertSession()->linkNotExists('Add teacher');

    $this->clickLink('Teachers');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->elementTextContains('css', 'table', 'Teacher Alpha');
    $this->assertSession()->elementTextContains('css', 'table', 'TB');
    $this->assertSession()->linkNotExists('Add teacher');
    $this->drupalGet('/admin/ancora/teachers/add');
    $this->assertSession()->statusCodeEquals(403);

    // The Views listing of the weekly activities.
    $this->drupalGet('/ancora/planning/activities');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->elementsCount('css', 'table tbody tr', 4);
    $this->assertSession()->elementTextContains('css', 'table', 'Subject Beta');
  }

  /**
   * Editors enter data through validated forms; plans are for administrators.
   */
  public function testEditorForms(): void {
    $this->drupalLogin($this->drupalCreateUser(['administer academic data']));
    $this->drupalGet('/admin/ancora/plans/add');
    $this->submitForm([
      'label[0][value]' => 'Autumn 2026',
      'code[0][value]' => 'AUT26',
      'duration_days[0][value]' => 0,
    ], 'Save');
    $this->assertSession()->pageTextContains('Length in days must be higher than or equal to 1.');
    $this->submitForm(['duration_days[0][value]' => 120], 'Save');
    $this->assertSession()->pageTextContains('Created the plan Autumn 2026.');

    $this->drupalLogin($this->drupalCreateUser(['view academic data', 'edit academic data']));
    $this->drupalGet('/admin/ancora/plans/add');
    $this->assertSession()->statusCodeEquals(403);

    $this->drupalGet('/admin/ancora/periods');
    $this->clickLink('Add period');
    // The only plan is preselected.
    $this->assertSession()->fieldValueEquals('plan', '1');
    $this->submitForm([
      'label[0][value]' => 'Week',
      'code[0][value]' => 'W',
      'days[0][value]' => 0,
      'slots_per_day[0][value]' => 6,
      'start_hour[0][value]' => 8,
      'slot_duration[0][value]' => 1,
    ], 'Save');
    $this->assertSession()->pageTextContains('Days must be higher than or equal to 1.');
    $this->submitForm(['days[0][value]' => 5], 'Save');
    $this->assertSession()->pageTextContains('Created the period Week.');
    $this->assertSession()->addressEquals('/admin/ancora/periods');
    $this->assertSession()->elementTextContains('css', 'table', 'AUT26');

    $this->drupalGet('/admin/ancora/teachers/add');
    $this->submitForm(['label[0][value]' => 'Ada Teacher', 'code[0][value]' => 'T1'], 'Save');
    $this->assertSession()->pageTextContains('Created the teacher Ada Teacher.');
    $this->drupalGet('/admin/ancora/teachers/add');
    $this->submitForm(['label[0][value]' => 'Another Teacher', 'code[0][value]' => 'T1'], 'Save');
    $this->assertSession()->pageTextContains('The code T1 is already used by another record of this plan.');

    // Edit, then delete a teacher nobody references.
    $this->drupalGet('/admin/ancora/teachers');
    $this->clickLink('Ada Teacher');
    $this->clickLink('Edit');
    $this->submitForm(['label[0][value]' => 'Ada Lovelace'], 'Save');
    $this->assertSession()->pageTextContains('Updated the teacher Ada Lovelace.');
    $this->clickLink('Ada Lovelace');
    $this->clickLink('Delete');
    $this->submitForm([], 'Delete');
    $this->assertSession()->pageTextContains('has been deleted');
    $this->assertSession()->elementTextContains('css', 'table', 'There are no teachers yet.');

    // Editors cannot delete plans.
    $this->drupalGet('/admin/ancora/plans/1/delete');
    $this->assertSession()->statusCodeEquals(403);
  }

  /**
   * A plan with records cannot be deleted from its form.
   */
  public function testPlanWithRecordsIsNotDeleted(): void {
    \Drupal::service(SyntheticFixture::class)->load();
    $this->drupalLogin($this->drupalCreateUser(['view academic data', 'administer academic data']));
    $this->drupalGet('/admin/ancora/plans/1/delete');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains('Plan cannot be deleted because it is still used by');
    $this->assertSession()->pageTextContains('4 activity templates');
    $this->assertSession()->buttonNotExists('Delete');

    $this->drupalGet('/admin/ancora/templates');
    $this->assertSession()->elementsCount('css', 'table tbody tr', 4);
    $this->drupalGet('/admin/ancora/templates', ['query' => ['plan' => 999]]);
    $this->assertSession()->elementTextContains('css', 'table', 'There are no activity templates yet.');
  }

}
