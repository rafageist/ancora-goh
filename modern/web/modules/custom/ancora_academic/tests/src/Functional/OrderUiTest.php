<?php

declare(strict_types=1);

namespace Drupal\Tests\ancora_academic\Functional;

use Drupal\ancora_academic\Fixture\SyntheticFixture;
use Drupal\Tests\ancora\Functional\AncoraBrowserTestBase;
use PHPUnit\Framework\Attributes\Group;

/**
 * The legacy priority and order upkeep over HTTP (#70, D13).
 */
#[Group('ancora')]
final class OrderUiTest extends AncoraBrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['ancora_academic', 'block'];

  /**
   * Hidden numbers, the arrows, a second planner without them.
   */
  public function testArrows(): void {
    $owner = $this->drupalCreateUser(['view academic data', 'edit academic data']);
    \Drupal::service(SyntheticFixture::class)->load(TRUE, $owner);
    $this->drupalLogin($owner);

    // A new activity type: no priority field; it gets count + 1.
    $this->drupalGet('/admin/ancora/activity-types/add');
    $this->assertSession()->fieldNotExists('priority[0][value]');
    $this->submitForm([
      'label[0][value]' => 'Lab',
      'code[0][value]' => 'C2',
      'duration_slots[0][value]' => 2,
      'cost[0][value]' => 1,
    ], 'Save');
    $storage = \Drupal::entityTypeManager()->getStorage('ancora_activity_type');
    $priority = static function (string $code) use ($storage): int {
      $storage->resetCache();
      return (int) current($storage->loadByProperties(['code' => $code]))->get('priority')->value;
    };
    $this->assertSame(2, $priority('C2'));

    // The list is in priority order, with the arrows.
    $this->drupalGet('/admin/ancora/activity-types');
    $this->clickLink('Move up', 1);
    $this->assertSession()->pageTextContains('Moved Lab.');
    $this->assertSame([1, 2], [$priority('C2'), $priority('C1')]);
    $this->clickLink('Move up', 0);
    $this->assertSession()->pageTextContains('Lab is already the first.');

    // Templates: no order field; the edit keeps the subject and period.
    $templates = \Drupal::entityTypeManager()->getStorage('ancora_activity_template')->loadMultiple();
    $template = array_key_first($templates);
    $this->drupalGet("/admin/ancora/templates/$template/edit");
    $this->assertSession()->fieldNotExists('sequence[0][value]');
    $this->assertSession()->fieldDisabled('subject');
    $this->assertSession()->fieldDisabled('period');
    $template_storage = \Drupal::entityTypeManager()->getStorage('ancora_activity_template');
    $sequences = static function () use ($template_storage): array {
      $template_storage->resetCache();
      $sequences = array_map(static fn ($t): int => (int) $t->get('sequence')->value, $template_storage->loadMultiple());
      ksort($sequences);
      return $sequences;
    };
    $before = $sequences();
    $this->assertSame(1, $before[$template]);
    $this->drupalGet('/admin/ancora/templates');
    $this->clickLink('Move down', 0);
    $this->assertSession()->pageTextContains('Moved');
    $moved = $sequences();
    $this->assertSame(2, $moved[$template]);

    // The same move from the breakdown list (legacy desglose_detalles.tpl
    // arrows, FC-17) goes through the same route and renumbers the same
    // way: moving it back up restores the order.
    $this->drupalGet('/admin/ancora/breakdowns');
    $this->clickLink('Move up Subject Alpha #2 (PER)');
    $this->assertSession()->pageTextContains('Moved Subject Alpha #2 (PER).');
    $this->assertSession()->addressEquals('/admin/ancora/breakdowns');
    $this->assertSame($before, $sequences());
    // The first template of a breakdown has no Move up there (legacy: no
    // left arrow on the first), the last one no Move down.
    $this->assertSession()->linkNotExists('Move up Subject Alpha #1 (PER)');
    $this->assertSession()->linkNotExists('Move down Subject Alpha #2 (PER)');

    // Another planner sees neither the records nor the arrows; the move
    // route refuses them.
    $this->drupalLogin($this->drupalCreateUser(['view academic data', 'edit academic data']));
    $this->drupalGet('/admin/ancora/activity-types');
    $this->assertSession()->linkNotExists('Move up');
    $this->drupalGet("/ancora/planning/order/template/$template/up");
    $this->assertSession()->statusCodeEquals(403);
  }

}
