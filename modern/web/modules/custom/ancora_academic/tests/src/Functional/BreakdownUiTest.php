<?php

declare(strict_types=1);

namespace Drupal\Tests\ancora_academic\Functional;

use Drupal\ancora_academic\Entity\Breakdown;
use Drupal\ancora_academic\Fixture\SyntheticFixture;
use Drupal\Tests\ancora\Functional\AncoraBrowserTestBase;
use PHPUnit\Framework\Attributes\Group;

/**
 * The subject breakdown options over HTTP (legacy form_desglose.tpl, D4).
 */
#[Group('ancora')]
final class BreakdownUiTest extends AncoraBrowserTestBase {

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
   * The owner lists and edits the options; another planner sees nothing.
   */
  public function testListAndEdit(): void {
    $owner = $this->drupalCreateUser(['view academic data', 'edit academic data']);
    \Drupal::service(SyntheticFixture::class)->load(TRUE, $owner);
    $storage = \Drupal::entityTypeManager()->getStorage('ancora_breakdown');
    $by_subject = [];
    foreach ($storage->loadMultiple() as $breakdown) {
      assert($breakdown instanceof Breakdown);
      $by_subject[$breakdown->getSubject()->getCode()] = $breakdown;
    }
    $this->assertSame(['A', 'B'], array_keys($by_subject));
    $alpha = $by_subject['A'];
    $id = $alpha->id();

    $this->drupalGet('/admin/ancora/breakdowns');
    $this->assertSession()->statusCodeEquals(403);

    $this->drupalLogin($owner);
    $this->drupalGet('/admin/ancora/breakdowns');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->elementsCount('css', 'table tbody tr', 2);
    $this->assertSession()->elementTextContains('css', 'table', 'Subject Alpha (Week)');
    // The templates of each breakdown in their order.
    $this->assertSession()->elementTextContains('css', 'table', '1 Lecture, 2 Lecture');
    // Breakdowns are created by the module, never by a user.
    $this->assertSession()->linkNotExists('Add subject breakdown');
    $this->drupalGet("/admin/ancora/breakdowns/$id/delete");
    $this->assertSession()->statusCodeEquals(404);

    // The subject page links to its breakdowns.
    $this->drupalGet('/admin/ancora/subjects/' . $alpha->getSubject()->id());
    $this->clickLink('Subject breakdowns (scheduling options)');
    $this->assertSession()->elementsCount('css', 'table tbody tr', 1);

    // The form: the plan, subject and period, then the four legacy options
    // with the legacy defaults.
    $this->drupalGet("/admin/ancora/breakdowns/$id/edit");
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains('Plan: Plan');
    $this->assertSession()->pageTextContains('Subject: Subject Alpha');
    $this->assertSession()->pageTextContains('Period: Week');
    $this->assertSession()->fieldValueEquals('min_days[0][value]', '1');
    $this->assertSession()->fieldValueEquals('max_days[0][value]', '5');
    $this->assertSession()->checkboxChecked('respect_order[value]');
    $this->assertSession()->checkboxNotChecked('same_day[value]');
    $this->assertSession()->fieldNotExists('subject');
    $this->assertSession()->fieldNotExists('period');
    $this->assertSession()->linkNotExists('Delete');

    // Empty is refused (the legacy handler cannot store it either).
    $this->submitForm(['min_days[0][value]' => ''], 'Save');
    $this->assertSession()->pageTextContains('Minimum number of days between activities field is required.');

    // Any integer is stored, including a minimum above the maximum.
    $this->submitForm([
      'min_days[0][value]' => '7',
      'max_days[0][value]' => '-2',
      'respect_order[value]' => FALSE,
      'same_day[value]' => TRUE,
    ], 'Save');
    $this->assertSession()->pageTextContains('Updated the breakdown of Subject Alpha (Week).');
    // Back to the list of the plan's breakdowns.
    $this->assertSession()->addressEquals('/admin/ancora/breakdowns');
    $this->assertMatchesRegularExpression('#/admin/ancora/breakdowns\?plan=\d+$#', $this->getSession()->getCurrentUrl());
    $storage->resetCache();
    $alpha = $storage->load($id);
    assert($alpha instanceof Breakdown);
    $this->assertSame(['min_dias' => 7, 'max_dias' => -2, 'respetar_orden' => FALSE, 'mismo_dia' => TRUE], $alpha->legacyOptions());

    // Another planner sees none of them and cannot open or edit one.
    $this->drupalLogin($this->drupalCreateUser(['view academic data', 'edit academic data']));
    $this->drupalGet('/admin/ancora/breakdowns');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains('There are no subject breakdowns yet.');
    foreach (["/admin/ancora/breakdowns/$id", "/admin/ancora/breakdowns/$id/edit"] as $path) {
      $this->drupalGet($path);
      $this->assertSession()->statusCodeEquals(403);
    }
  }

}
