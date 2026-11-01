<?php

declare(strict_types=1);

namespace Drupal\Tests\ancora_academic\Functional;

use Drupal\ancora_academic\Fixture\SyntheticFixture;
use Drupal\ancora_academic\RoomConstraints;
use Drupal\Tests\ancora\Functional\AncoraBrowserTestBase;
use PHPUnit\Framework\Attributes\Group;

/**
 * The fixed/forbidden room and travel cost editors over HTTP (#70, D9c).
 */
#[Group('ancora')]
final class RoomConstraintsUiTest extends AncoraBrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['ancora_academic', 'block'];

  /**
   * The id of a record by type and code.
   */
  private function id(string $entity_type_id, string $code): int {
    return (int) array_key_first(\Drupal::entityTypeManager()->getStorage($entity_type_id)->loadByProperties(['code' => $code]));
  }

  /**
   * Both editors; a second planner sees none of the data.
   */
  public function testEditors(): void {
    $owner = $this->drupalCreateUser(['view academic data', 'edit academic data']);
    \Drupal::service(SyntheticFixture::class)->load(TRUE, $owner);
    $group = $this->id('ancora_student_group', 'G1');
    $l1 = $this->id('ancora_room', 'L1');
    $l2 = $this->id('ancora_room', 'L2');
    $rooms = \Drupal::service(RoomConstraints::class);
    $this->drupalLogin($owner);

    // Fixed and forbidden rooms, from the group page.
    $this->drupalGet("/admin/ancora/groups/$group");
    $this->clickLink('Fixed and forbidden rooms');
    $this->submitForm(['fixed' => $l2, "forbidden[$l1]" => TRUE], 'Save');
    $this->assertSession()->pageTextContains('Saved.');
    $this->assertSame([$l2], $rooms->groupRooms($group, 'fixed'));
    $this->assertSame([$l1], $rooms->groupRooms($group, 'forbidden'));
    $this->assertSession()->fieldValueEquals('fixed', (string) $l2);

    // Travel costs, from the room page: one select 0..999 per other room.
    $this->drupalGet("/admin/ancora/rooms/$l1");
    $this->clickLink('Travel costs');
    $this->assertSession()->optionExists("costs[$l2][cost]", '999');
    $this->assertSession()->fieldNotExists("costs[$l1][cost]");
    $this->submitForm(["costs[$l2][cost]" => '12'], 'Save');
    $this->assertSame([$l2 => 12], $rooms->travelCosts($l1));
    $this->submitForm(["costs[$l2][cost]" => '0'], 'Save');
    $this->assertSame([], $rooms->travelCosts($l1), '0 is no row');

    // Another planner: no editor for these records.
    $this->drupalLogin($this->drupalCreateUser(['view academic data', 'edit academic data']));
    $this->drupalGet('/ancora/planning/group-rooms', ['query' => ['group' => $group]]);
    $this->assertSession()->pageTextContains('Open this page from the page of one of your student groups.');
    $this->drupalGet('/ancora/planning/travel-costs', ['query' => ['room' => $l1]]);
    $this->assertSession()->pageTextContains('Open this page from the page of one of your rooms.');
    $this->assertSession()->buttonNotExists('Save');
  }

}
