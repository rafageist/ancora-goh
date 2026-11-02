<?php

declare(strict_types=1);

namespace Drupal\Tests\ancora_academic\Functional;

use Behat\Mink\Element\NodeElement;
use Drupal\ancora_academic\Entity\ActivityTemplate;
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
    $this->assertSession()->elementsCount('css', 'table ol.ancora-breakdown-templates', 2);
    $this->assertSession()->elementsCount('css', 'table ol.ancora-breakdown-templates > li', 4);
    $this->assertSession()->elementTextContains('css', 'table ol.ancora-breakdown-templates > li:first-child', '1 Lecture');
    $this->assertSession()->elementTextContains('css', 'table ol.ancora-breakdown-templates > li:last-child', '2 Lecture');
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

  /**
   * Returns the breakdown of a subject code of an owner.
   */
  private function breakdownOf(string $subject_code, int $owner): Breakdown {
    $properties = ['code' => $subject_code, 'uid' => $owner];
    $subjects = \Drupal::entityTypeManager()->getStorage('ancora_subject')->loadByProperties($properties);
    $this->assertCount(1, $subjects);
    $breakdowns = \Drupal::entityTypeManager()->getStorage('ancora_breakdown')->loadByProperties(['subject' => key($subjects)]);
    $this->assertCount(1, $breakdowns);
    $breakdown = reset($breakdowns);
    assert($breakdown instanceof Breakdown);
    return $breakdown;
  }

  /**
   * The template IDs of a breakdown, by their order.
   *
   * @return array<int, int>
   *   Order => template ID.
   */
  private function orderOf(Breakdown $breakdown): array {
    $storage = \Drupal::entityTypeManager()->getStorage('ancora_activity_template');
    $storage->resetCache();
    $order = [];
    $properties = [
      'subject' => $breakdown->get('subject')->target_id,
      'period' => $breakdown->get('period')->target_id,
    ];
    foreach ($storage->loadByProperties($properties) as $template) {
      assert($template instanceof ActivityTemplate);
      $order[$template->getSequence()] = (int) $template->id();
    }
    ksort($order);
    return $order;
  }

  /**
   * Finds the one link whose accessible name is exactly $name.
   */
  private function linkNamed(string $name): NodeElement {
    $links = $this->getSession()->getPage()->findAll('xpath', '//a[normalize-space(.)=' . $this->assertSession()->buildXPathQuery(':n', [':n' => $name]) . ']');
    $this->assertCount(1, $links, "one link named \"$name\"");
    return reset($links);
  }

  /**
   * The templates of a breakdown, in place (legacy desglose_detalles.tpl).
   *
   * Without JavaScript: every action is a link or a plain form.
   */
  public function testTemplatesInPlace(): void {
    $owner = $this->drupalCreateUser(['view academic data', 'edit academic data']);
    $plan = \Drupal::service(SyntheticFixture::class)->load(TRUE, $owner);
    $alpha = $this->breakdownOf('A', (int) $owner->id());
    [1 => $a1, 2 => $a2] = $this->orderOf($alpha);
    $list = '/admin/ancora/breakdowns?plan=' . $plan->id();
    $this->drupalLogin($owner);
    $this->drupalGet('/admin/ancora/breakdowns', ['query' => ['plan' => $plan->id()]]);

    // Each template links to its page, with the legacy arrows (no left one
    // on the first, no right one on the last) and its editors; the
    // accessible names say which template they act on.
    $this->assertSession()->linkByHrefExists("/admin/ancora/templates/$a1");
    $this->assertSession()->elementsCount('xpath', '//table//ol[contains(@class, "ancora-breakdown-templates")]/li[1]//a[starts-with(normalize-space(.), "Move up")]', 0);
    $this->assertSession()->elementsCount('xpath', '//table//ol[contains(@class, "ancora-breakdown-templates")]/li[2]//a[starts-with(normalize-space(.), "Move down")]', 0);
    $this->assertSession()->elementsCount('css', '.ancora-breakdown-templates__actions a', 4 * 3);
    $this->assertSession()->elementTextEquals('css', '.ancora-breakdown-templates__actions a .visually-hidden', 'Subject Alpha #1 (PER)');
    $down = $this->linkNamed('Move down Subject Alpha #1 (PER)');
    $this->assertStringContainsString("/ancora/planning/order/template/$a1/down?", $down->getAttribute('href'));
    $this->assertStringContainsString('token=', $down->getAttribute('href'));
    $this->assertStringContainsString('destination=/admin/ancora/breakdowns%3Fplan%3D' . $plan->id() . '&', $down->getAttribute('href'));
    $this->linkNamed('Move up Subject Alpha #2 (PER)');
    $this->assertStringEndsWith("/ancora/planning/assignment-editor?template=$a1", $this->linkNamed('Teachers, groups and rooms of Subject Alpha #1 (PER)')->getAttribute('href'));
    $this->assertStringEndsWith("/ancora/planning/template-resources?template=$a2", $this->linkNamed('Other resources of Subject Alpha #2 (PER)')->getAttribute('href'));
    $add = $this->linkNamed('Add activity template to Subject Alpha (Week)');
    $this->assertStringContainsString('/admin/ancora/templates/add?breakdown=' . $alpha->id() . '&destination=', $add->getAttribute('href'));

    // The editors open on the template.
    $this->linkNamed('Teachers, groups and rooms of Subject Alpha #1 (PER)')->click();
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains('Teachers (1)');
    $this->drupalGet('/admin/ancora/breakdowns', ['query' => ['plan' => $plan->id()]]);
    $this->linkNamed('Other resources of Subject Alpha #2 (PER)')->click();
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains('The other resources Subject Alpha #2 (PER) needs');

    // Move down: the existing D13 move, back to the same list.
    $this->drupalGet('/admin/ancora/breakdowns', ['query' => ['plan' => $plan->id()]]);
    $this->linkNamed('Move down Subject Alpha #1 (PER)')->click();
    $this->assertSession()->pageTextContains('Moved Subject Alpha #1 (PER).');
    $this->assertStringEndsWith($list, $this->getSession()->getCurrentUrl());
    $this->assertSame([1 => $a2, 2 => $a1], $this->orderOf($alpha));
    $this->assertSession()->linkByHrefExists("/ancora/planning/order/template/$a2/down");
    $this->assertSession()->linkByHrefNotExists("/ancora/planning/order/template/$a2/up");

    // The move needs the CSRF token: none or a forged one is refused and
    // changes nothing.
    foreach ([[], ['token' => 'forged']] as $query) {
      $this->drupalGet("/ancora/planning/order/template/$a2/down", ['query' => $query]);
      $this->assertSession()->statusCodeEquals(403);
    }
    $this->assertSame([1 => $a2, 2 => $a1], $this->orderOf($alpha));

    // "Add activity template": plan, subject and period come from the
    // breakdown and are locked; the new template is the last (count + 1).
    $this->drupalGet('/admin/ancora/breakdowns', ['query' => ['plan' => $plan->id()]]);
    $this->linkNamed('Add activity template to Subject Alpha (Week)')->click();
    $this->assertSession()->statusCodeEquals(200);
    $locked = [
      'plan' => (int) $plan->id(),
      'subject' => (int) $alpha->get('subject')->target_id,
      'period' => (int) $alpha->get('period')->target_id,
    ];
    foreach ($locked as $field => $value) {
      $this->assertSession()->fieldDisabled($field);
      $this->assertSession()->fieldValueEquals($field, (string) $value);
    }
    $this->assertSession()->fieldNotExists('sequence[0][value]');
    $properties = ['code' => 'C1', 'uid' => $owner->id()];
    $types = \Drupal::entityTypeManager()->getStorage('ancora_activity_type')->loadByProperties($properties);
    $this->submitForm(['activity_type' => key($types), 'requires_room[value]' => FALSE], 'Save');
    $this->assertSession()->pageTextContains('Created the activity template Subject Alpha #3 (PER).');
    $this->assertSession()->addressEquals('/admin/ancora/breakdowns');
    $order = $this->orderOf($alpha);
    $this->assertSame([1 => $a2, 2 => $a1], array_slice($order, 0, 2, TRUE));
    $this->assertCount(3, $order);
    $new = \Drupal::entityTypeManager()->getStorage('ancora_activity_template')->load($order[3]);
    assert($new instanceof ActivityTemplate);
    $this->assertSame($locked + ['requires_room' => FALSE], [
      'plan' => (int) $new->get('plan')->target_id,
      'subject' => (int) $new->get('subject')->target_id,
      'period' => (int) $new->get('period')->target_id,
      'requires_room' => $new->requiresRoom(),
    ]);
    $this->linkNamed('Move up Subject Alpha #3 (PER)');
    $this->assertSession()->elementTextContains('xpath', '//tr[.//a[normalize-space(.)="Add activity template to Subject Alpha (Week)"]]//ol/li[3]', '3 Lecture');

    // A malformed, unknown or foreign breakdown gives the plain form,
    // with nothing of the breakdown in it.
    $other = $this->drupalCreateUser(['view academic data', 'edit academic data']);
    \Drupal::service(SyntheticFixture::class)->load(TRUE, $other);
    $foreign = $this->breakdownOf('A', (int) $other->id());
    foreach (['abc', '-1', '1e3', '999999', (string) $foreign->id()] as $value) {
      $this->drupalGet('/admin/ancora/templates/add', ['query' => ['breakdown' => $value]]);
      $this->assertSession()->statusCodeEquals(200);
      $this->assertSession()->fieldEnabled('subject');
      $this->assertSession()->fieldEnabled('period');
      $this->assertSession()->fieldEnabled('plan');
      $this->assertSession()->fieldValueEquals('subject', '_none');
    }
    $this->assertSession()->optionNotExists('subject', (string) $foreign->get('subject')->target_id);

    // The second planner sees only their own breakdowns and actions, and
    // the owner's breakdown gives them the plain form.
    $this->drupalLogin($other);
    $this->drupalGet('/admin/ancora/breakdowns');
    $this->assertSession()->elementsCount('css', 'table tbody tr', 2);
    $this->assertSession()->linkByHrefNotExists("/admin/ancora/templates/$a1");
    $this->assertSession()->linkByHrefNotExists("/ancora/planning/order/template/$a1/");
    $this->assertSession()->linkByHrefNotExists('breakdown=' . $alpha->id());
    $this->drupalGet('/admin/ancora/templates/add', ['query' => ['breakdown' => $alpha->id()]]);
    $this->assertSession()->fieldEnabled('subject');
    $this->assertSession()->optionNotExists('subject', (string) $alpha->get('subject')->target_id);
    $this->drupalGet('/ancora/planning/assignment-editor', ['query' => ['template' => $a1]]);
    $this->assertSession()->statusCodeEquals(403);
    $this->drupalGet('/ancora/planning/template-resources', ['query' => ['template' => $a1]]);
    $this->assertSession()->pageTextContains('Open this page from the page of one of your activity templates.');
    $this->assertSame([1 => $a2, 2 => $a1, 3 => $order[3]], $this->orderOf($alpha));

    // A planner who may only view their breakdowns sees the templates and
    // no write action.
    $viewer = $this->drupalCreateUser(['view academic data']);
    \Drupal::service(SyntheticFixture::class)->load(TRUE, $viewer);
    $this->drupalLogin($viewer);
    $this->drupalGet('/admin/ancora/breakdowns');
    $this->assertSession()->elementsCount('css', 'table ol.ancora-breakdown-templates > li', 4);
    $this->assertSession()->elementNotExists('css', '.ancora-breakdown-templates__actions');
    $this->assertSession()->elementNotExists('css', '.ancora-breakdown-templates__add');
    $this->assertSession()->linkNotExists('Move up');
  }

}
