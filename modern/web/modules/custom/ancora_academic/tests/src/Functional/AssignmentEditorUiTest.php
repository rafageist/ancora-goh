<?php

declare(strict_types=1);

namespace Drupal\Tests\ancora_academic\Functional;

use Drupal\ancora_academic\Entity\TeacherAssignment;
use Drupal\ancora_academic\Fixture\SyntheticFixture;
use Drupal\ancora_academic\TemplateAssignments;
use Drupal\Tests\ancora\Functional\AncoraBrowserTestBase;
use PHPUnit\Framework\Attributes\Group;

/**
 * The visual assignment editor over HTTP, without JavaScript (#122).
 *
 * Every action through its button (the keyboard path), and the drop path
 * through the same form fields that assignment-editor.js fills.
 */
#[Group('ancora')]
final class AssignmentEditorUiTest extends AncoraBrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['ancora_academic', 'block'];

  /**
   * The id of the owner's record of a type with a code.
   */
  private function id(string $type, string $code): int {
    return (int) array_key_first(\Drupal::entityTypeManager()->getStorage($type)->loadByProperties(['code' => $code]));
  }

  /**
   * The template of a subject code and number.
   */
  private function template(string $subject, int $sequence): int {
    $storage = \Drupal::entityTypeManager()->getStorage('ancora_activity_template');
    $storage->resetCache();
    $ids = $storage->getQuery()->accessCheck(FALSE)->condition('subject', $this->id('ancora_subject', $subject))->condition('sequence', $sequence)->execute();
    return (int) reset($ids);
  }

  /**
   * A fresh teacher assignment.
   */
  private function teacherAssignment(int $id): ?TeacherAssignment {
    $storage = \Drupal::entityTypeManager()->getStorage('ancora_teacher_assignment');
    $storage->resetCache();
    return $storage->load($id);
  }

  /**
   * Presses the drop button with the fields the script fills.
   */
  private function drop(array $values): void {
    $page = $this->getSession()->getPage();
    foreach ($values as $name => $value) {
      $page->find('css', "input[name=\"drop[$name]\"]")->setValue((string) $value);
    }
    $page->pressButton('op_drop');
  }

  /**
   * The journey: assign, prioritize, group, remove, undo, order.
   */
  public function testJourney(): void {
    $owner = $this->drupalCreateUser(['view academic data', 'edit academic data']);
    \Drupal::service(SyntheticFixture::class)->load(TRUE, $owner);
    $a1 = $this->template('A', 1);
    $tb = $this->id('ancora_teacher', 'TB');
    $g1 = $this->id('ancora_student_group', 'G1');
    $g2 = $this->id('ancora_student_group', 'G2');
    $this->drupalLogin($owner);

    // From the template page.
    $this->drupalGet("/admin/ancora/templates/$a1");
    $this->clickLink('Teachers, groups and rooms');
    $this->assertSession()->addressEquals('/ancora/planning/assignment-editor');
    $this->assertSession()->pageTextContains('Teachers (1)');
    $this->assertSession()->pageTextContains('Rooms (2)');
    $this->assertSession()->elementTextContains('css', '.ancora-assign__template', 'Group 1');
    $this->assertSession()->elementTextContains('css', '.ancora-assign__template', 'enrolment 20');
    $this->assertSession()->elementTextContains('css', '#ancora-assign-room-panel', 'Capacity 50');
    $this->assertSession()->elementTextContains('css', '.ancora-assign__available', 'Teacher Beta');
    $this->assertSession()->elementTextContains('css', '.ancora-assign__available', 'None left.');
    // Accessible names of the controls, and the drop fields.
    $this->assertSession()->elementExists('css', "input[aria-label=\"Assign Teacher Beta\"][name=\"op_assign_teacher_$tb\"]");
    $this->assertSession()->elementExists('css', '[role="region"][aria-labelledby="ancora-assign-teacher-panel-title"]');
    $this->assertSession()->elementExists('css', '[data-drag-kind="teacher"][data-id="' . $tb . '"][draggable="true"]');
    $this->assertSession()->elementExists('css', 'input[name="op_drop"][tabindex="-1"]');

    // Assign by button: priority 1, no group yet.
    $this->getSession()->getPage()->pressButton("op_assign_teacher_$tb");
    $this->assertSession()->elementTextContains('css', '#ancora-assign-feedback[role="status"]', 'Assigned Teacher Beta with priority 1.');
    $service = \Drupal::service(TemplateAssignments::class);
    $template = \Drupal::entityTypeManager()->getStorage('ancora_activity_template')->load($a1);
    $assignments = $service->assignments($template, 'teacher');
    $aid = (int) array_key_last($assignments);
    $this->assertSame([1, []], [
      $this->teacherAssignment($aid)->getPriority(),
      $this->teacherAssignment($aid)->getGroupIds(),
    ]);
    $this->assertSession()->elementTextContains('css', "#ancora-assign-teacher-$aid", 'No group yet: the engine never places this activity with this teacher.');
    $this->assertSession()->elementExists('css', "label:contains('Priority of Teacher Beta')");

    // Priority by button and select (the legacy 1..max + 1).
    $this->assertSession()->optionExists("priority[teacher][$aid]", '2');
    $this->assertSession()->optionNotExists("priority[teacher][$aid]", '3');
    $this->getSession()->getPage()->selectFieldOption("priority[teacher][$aid]", '2');
    $this->getSession()->getPage()->pressButton("op_priority_teacher_$aid");
    $this->assertSession()->pageTextContains('Teacher Beta now has priority 2.');
    $this->assertSession()->elementExists('css', "[data-priority=\"2\"] #ancora-assign-teacher-$aid");
    $this->assertSame(2, $this->teacherAssignment($aid)->getPriority());

    // Groups by button: add G2, remove it again.
    $this->getSession()->getPage()->selectFieldOption("add_group[$aid]", (string) $g2);
    $this->getSession()->getPage()->pressButton("op_add_group_teacher_$aid");
    $this->assertSession()->pageTextContains('Teacher Beta now gives the activity to Group 2.');
    $this->assertSame([$g2], $this->teacherAssignment($aid)->getGroupIds());
    $this->assertSession()->elementExists('css', "input[aria-label=\"Remove group Group 2 from Teacher Beta\"]");
    $this->getSession()->getPage()->pressButton("op_remove_group_teacher_{$aid}_$g2");
    $this->assertSession()->pageTextContains('Teacher Beta no longer gives the activity to Group 2.');
    $this->assertSame([], $this->teacherAssignment($aid)->getGroupIds());

    // The drop path: a group onto the teacher, the teacher onto priority 1.
    $this->drop(['op' => 'add_group', 'target' => $aid, 'item' => $g1]);
    $this->assertSession()->pageTextContains('Teacher Beta now gives the activity to Group 1.');
    $this->drop(['op' => 'priority', 'target' => 'teacher', 'item' => $aid, 'value' => 1]);
    $this->assertSession()->pageTextContains('Teacher Beta now has priority 1.');
    $this->assertSame([1, [$g1]], [
      $this->teacherAssignment($aid)->getPriority(),
      $this->teacherAssignment($aid)->getGroupIds(),
    ]);
    // Invalid drops change nothing.
    $this->drop(['op' => 'add_group', 'target' => $aid, 'item' => $g1]);
    $this->assertSession()->elementTextContains('css', '#ancora-assign-feedback[role="alert"]', 'Teacher Beta already gives the activity to that group.');
    $this->drop(['op' => 'priority', 'target' => 'teacher', 'item' => $aid, 'value' => 9]);
    $this->assertSession()->pageTextContains('The priority must be one of 1, 2.');
    $this->drop(['op' => 'bogus', 'target' => 'teacher', 'item' => $aid]);
    $this->assertSession()->pageTextContains('That change is not possible here.');
    $this->drop(['op' => 'assign', 'target' => 'teacher', 'item' => $this->id('ancora_teacher', 'TA')]);
    $this->assertSession()->pageTextContains('This teacher is already assigned to this activity template.');
    $b1_assignment = (int) array_key_first($service->assignments(\Drupal::entityTypeManager()->getStorage('ancora_activity_template')->load($this->template('B', 1)), 'teacher'));
    $this->drop(['op' => 'priority', 'target' => 'teacher', 'item' => $b1_assignment, 'value' => 1]);
    $this->assertSession()->pageTextContains('That assignment is not one of this template.');
    $this->assertSame([1, [$g1]], [
      $this->teacherAssignment($aid)->getPriority(),
      $this->teacherAssignment($aid)->getGroupIds(),
    ]);

    // Remove asks first; cancel keeps it; confirm removes; undo restores.
    $this->getSession()->getPage()->pressButton("op_remove_teacher_$aid");
    $this->assertSession()->pageTextContains('Remove Teacher Beta from this template?');
    $this->assertNotNull($this->teacherAssignment($aid), 'nothing removed before the confirmation');
    $this->getSession()->getPage()->pressButton('Cancel');
    $this->assertSession()->pageTextNotContains('Remove Teacher Beta from this template?');
    $this->getSession()->getPage()->pressButton("op_remove_teacher_$aid");
    $this->getSession()->getPage()->pressButton('Confirm removal');
    $this->assertSession()->pageTextContains('Removed Teacher Beta from this template.');
    $this->assertNull($this->teacherAssignment($aid));
    $this->assertSession()->elementTextContains('css', '.ancora-assign__available', 'Teacher Beta');
    $this->getSession()->getPage()->pressButton('Undo: assign Teacher Beta again');
    $this->assertSession()->pageTextContains('Assigned Teacher Beta again.');
    $restored = $service->assignments($template, 'teacher');
    $again = end($restored);
    $this->assertSame([$tb, 1, [$g1]], [
      (int) $again->get('teacher')->target_id,
      $again->getPriority(),
      $again->getGroupIds(),
    ]);
    $this->assertSession()->buttonNotExists('Undo: assign Teacher Beta again');

    // A removal by drop (the script confirms first).
    $rooms = $service->assignments($template, 'room');
    $l2 = (int) array_key_last($rooms);
    $this->drop(['op' => 'remove', 'target' => 'room', 'item' => $l2, 'confirmed' => '1']);
    $this->assertSession()->pageTextContains('Removed Room 2 from this template.');
    $this->assertSession()->elementTextContains('css', '.ancora-assign__available', 'Room 2');

    // A change made meanwhile (another window): refused, nothing changed.
    $again = $this->teacherAssignment((int) $again->id());
    $service->setPriority($template, $again, 2);
    $this->getSession()->getPage()->pressButton('op_assign_room_' . $this->id('ancora_room', 'L2'));
    $this->assertSession()->pageTextContains('The assignments of this template changed after this page was loaded');
    $this->assertCount(1, $service->assignments($template, 'room'));
    $this->getSession()->getPage()->pressButton('op_assign_room_' . $this->id('ancora_room', 'L2'));
    $this->assertSession()->pageTextContains('Assigned Room 2 with priority 1.');

    // Details, as a page (a dialog with the script).
    $this->assertSession()->elementExists('css', "a.use-ajax[data-dialog-type=\"dialog\"][href*=\"/details/teacher/{$again->id()}\"]");
    $this->drupalGet("/ancora/planning/assignment-editor/details/teacher/{$again->id()}");
    $this->assertSession()->pageTextContains('Priority: 2');
    $this->assertSession()->pageTextContains('Group Group 1: enrolment 20');

    // Search, keeping the context on the way back.
    $this->drupalGet('/ancora/planning/assignment-editor', ['query' => ['template' => $a1, 'q' => 'zzz']]);
    $this->assertSession()->pageTextContains('None matches the search.');
    $this->assertSession()->linkByHrefExists("/admin/ancora/templates/$a1");
    $this->assertSession()->linkByHrefExists('/ancora/planning/assignment-editor?q=zzz');

    // The template list and the D13 arrows.
    $this->drupalGet('/ancora/planning/assignment-editor');
    $this->assertSession()->pageTextContains('2 teachers, 2 rooms');
    $a2 = $this->template('A', 2);
    $this->clickLink('Move ' . \Drupal::entityTypeManager()->getStorage('ancora_activity_template')->load($a2)->label() . ' up');
    $this->assertSession()->addressEquals('/ancora/planning/assignment-editor');
    $storage = \Drupal::entityTypeManager()->getStorage('ancora_activity_template');
    $storage->resetCache();
    $this->assertSame([2, 1], [
      (int) $storage->load($a1)->get('sequence')->value,
      (int) $storage->load($a2)->get('sequence')->value,
    ]);
  }

  /**
   * Other planners, anonymous visitors and forged ids.
   */
  public function testAccess(): void {
    $owner = $this->drupalCreateUser(['view academic data', 'edit academic data']);
    \Drupal::service(SyntheticFixture::class)->load(TRUE, $owner);
    $a1 = $this->template('A', 1);
    $assignment = (int) array_key_first(\Drupal::entityTypeManager()->getStorage('ancora_teacher_assignment')->loadByProperties(['template' => $a1]));

    $other = $this->drupalCreateUser(['view academic data', 'edit academic data']);
    $this->drupalLogin($other);
    $this->drupalGet('/ancora/planning/assignment-editor', ['query' => ['template' => $a1]]);
    $this->assertSession()->statusCodeEquals(403);
    $this->drupalGet("/ancora/planning/assignment-editor/details/teacher/$assignment");
    $this->assertSession()->statusCodeEquals(403);
    $this->drupalGet('/ancora/planning/assignment-editor');
    $this->assertSession()->pageTextContains('You have no activity template yet.');
    $this->assertSession()->pageTextNotContains('Subject Alpha');

    // Another planner's own teacher, forged into the owner's editor, is
    // refused by the service even if the owner's page posts it.
    $foreign = \Drupal::entityTypeManager()->getStorage('ancora_teacher')->create([
      'label' => 'Foreign',
      'code' => 'TF',
      'uid' => $other->id(),
    ]);
    $foreign->save();
    $this->drupalLogin($owner);
    $this->drupalGet('/ancora/planning/assignment-editor', ['query' => ['template' => $a1]]);
    $this->drop(['op' => 'assign', 'target' => 'teacher', 'item' => $foreign->id()]);
    $this->assertSession()->pageTextContains('That teacher is not one of the teachers of the owner of this template.');

    foreach (['x', '999999', '1 OR 1=1'] as $id) {
      $this->drupalGet('/ancora/planning/assignment-editor', ['query' => ['template' => $id]]);
      $this->assertSession()->statusCodeEquals(404);
    }
    $this->drupalGet('/ancora/planning/assignment-editor/details/teacher/999999');
    $this->assertSession()->statusCodeEquals(404);

    // Viewers without "edit academic data", and anonymous visitors.
    $this->drupalLogin($this->drupalCreateUser(['view academic data']));
    $this->drupalGet('/ancora/planning/assignment-editor', ['query' => ['template' => $a1]]);
    $this->assertSession()->statusCodeEquals(403);
    $this->drupalLogout();
    $this->drupalGet('/ancora/planning/assignment-editor', ['query' => ['template' => $a1]]);
    $this->assertSession()->statusCodeEquals(403);
  }

}
