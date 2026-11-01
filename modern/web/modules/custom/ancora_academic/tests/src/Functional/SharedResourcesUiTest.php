<?php

declare(strict_types=1);

namespace Drupal\Tests\ancora_academic\Functional;

use Drupal\ancora_academic\Fixture\SyntheticFixture;
use Drupal\ancora_academic\SharedResources;
use Drupal\Tests\ancora\Functional\AncoraBrowserTestBase;
use PHPUnit\Framework\Attributes\Group;

/**
 * The other resources catalogue and a template's resource editor (#70, D9d).
 */
#[Group('ancora')]
final class SharedResourcesUiTest extends AncoraBrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['ancora_academic', 'block'];

  /**
   * Catalogue, editor from the template page; a second planner sees nothing.
   */
  public function testEditor(): void {
    $owner = $this->drupalCreateUser(['view academic data', 'edit academic data']);
    \Drupal::service(SyntheticFixture::class)->load(TRUE, $owner);
    $this->drupalLogin($owner);

    // The catalogue, as every other one.
    $this->drupalGet('/admin/ancora/other-resources/add');
    $this->submitForm(['label[0][value]' => 'Projector 1', 'code[0][value]' => 'R1'], 'Save');
    $this->drupalGet('/admin/ancora/other-resources/add');
    $this->submitForm(['label[0][value]' => 'Projector 2', 'code[0][value]' => 'R2'], 'Save');
    $this->drupalGet('/admin/ancora/other-resources');
    $this->assertSession()->pageTextContains('Projector 2');
    $resources = \Drupal::entityTypeManager()->getStorage('ancora_other_resource')->loadByProperties(['uid' => $owner->id()]);
    $this->assertCount(2, $resources);
    [$r1, $r2] = array_map('intval', array_keys($resources));

    // The editor, from the template page.
    $templates = \Drupal::entityTypeManager()->getStorage('ancora_activity_template')->loadMultiple();
    $template = (int) array_key_first($templates);
    $this->drupalGet("/admin/ancora/templates/$template");
    $this->clickLink('Other resources');
    $this->assertSession()->pageTextContains('The template needs no other resource.');
    $this->submitForm([], 'Add a group');
    $this->assertSession()->pageTextContains('Group 1');
    $shared = \Drupal::service(SharedResources::class);
    $group = (int) array_key_first($shared->groups($template));
    $this->assertSession()->checkboxChecked("groups[$group][essential]");
    $this->assertSession()->fieldValueEquals("groups[$group][needed]", '1');
    $this->submitForm(["groups[$group][add][$r1]" => TRUE, "groups[$group][add][$r2]" => TRUE], 'Save group 1');
    $this->assertSession()->pageTextContains('Saved.');
    $this->assertSession()->pageTextContains('Projector 1 (priority 1)');
    $this->assertSession()->optionExists("groups[$group][needed]", '2');
    $this->submitForm(["groups[$group][needed]" => '2', "groups[$group][essential]" => FALSE], 'Save group 1');
    \Drupal::entityTypeManager()->getStorage('ancora_resource_group')->resetCache();
    $saved = $shared->groups($template)[$group];
    $this->assertSame([2, FALSE], [(int) $saved->get('needed')->value, (bool) $saved->get('essential')->value]);
    $this->submitForm(["groups[$group][remove][" . array_key_first($shared->members($group)) . ']' => TRUE], 'Save group 1');
    $this->assertSession()->pageTextNotContains('Projector 1 (priority 1)');
    $this->assertCount(1, $shared->members($group));

    // Another planner: no editor for this template, no resource listed.
    $this->drupalLogin($this->drupalCreateUser(['view academic data', 'edit academic data']));
    $this->drupalGet('/ancora/planning/template-resources', ['query' => ['template' => $template]]);
    $this->assertSession()->pageTextContains('Open this page from the page of one of your activity templates.');
    $this->assertSession()->buttonNotExists('Add a group');
    $this->drupalGet('/admin/ancora/other-resources');
    $this->assertSession()->pageTextNotContains('Projector 2');

    // The owner deletes the group.
    $this->drupalLogin($owner);
    $this->drupalGet('/ancora/planning/template-resources', ['query' => ['template' => $template]]);
    $this->submitForm([], 'Delete group 1');
    $this->assertSame([], $shared->groups($template));
    $this->assertSession()->pageTextContains('The template needs no other resource.');
  }

}
