<?php

declare(strict_types=1);

namespace Drupal\Tests\ancora_academic\Kernel;

use Drupal\ancora_academic\Entity\Room;
use Drupal\ancora_academic\Entity\StudentGroup;
use Drupal\ancora_academic\Export\AcademicExport;
use Drupal\ancora_academic\Export\LegacyRows;
use Drupal\ancora_academic\Hook\RoomPriorityHooks;
use Drupal\ancora_academic\RoomConstraints;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;

/**
 * Fixed and forbidden rooms, travel costs, room priorities (D9c).
 *
 * Legacy (legacy-parity-inventory.md PAR-055, 060, 061, 063; case 141).
 */
#[CoversClass(RoomConstraints::class)]
#[CoversClass(RoomPriorityHooks::class)]
#[Group('ancora')]
final class RoomConstraintsTest extends AcademicKernelTestBase {

  /**
   * A record by type and code.
   */
  private function record(string $entity_type_id, string $code): mixed {
    $this->container->get('entity_type.manager')->getStorage($entity_type_id)->resetCache();
    return $this->byCode($entity_type_id, $code);
  }

  /**
   * Saves and the legacy rows.
   */
  public function testSavesAndLegacyRows(): void {
    $plan = $this->loadFixture();
    $rooms = $this->container->get(RoomConstraints::class);
    $g1 = $this->record('ancora_student_group', 'G1');
    assert($g1 instanceof StudentGroup);
    $l1 = $this->record('ancora_room', 'L1');
    $l2 = $this->record('ancora_room', 'L2');
    assert($l1 instanceof Room && $l2 instanceof Room);

    $this->assertSame([], $rooms->saveGroupRooms($g1, (int) $l2->id(), [(int) $l1->id()]));
    $this->assertSame([(int) $l2->id()], $rooms->groupRooms($g1->id(), 'fixed'));
    $this->assertSame([], $rooms->saveTravelCosts($l1, [(int) $l2->id() => 7]));
    $this->assertSame([], $rooms->saveTravelCosts($l2, [(int) $l1->id() => 0]));
    $this->assertSame([(int) $l2->id() => 7], $rooms->travelCosts($l1->id()));
    $this->assertSame([], $rooms->travelCosts($l2->id()), 'cost 0 is no row');

    $rows = $this->container->get(LegacyRows::class)->build($this->container->get(AcademicExport::class)->export($plan));
    $this->assertSame([['grupo' => 'G1', 'lugar' => 'L2']], $rows['lugar_fijo']);
    $this->assertSame([['grupo' => 'G1', 'lugar' => 'L1']], $rows['acceso_prohibido']);
    $this->assertSame([['origen' => 'L1', 'destino' => 'L2', 'costo' => 7]], $rows['costo_de_traslado']);

    // A new save replaces the group's rooms.
    $this->assertSame([], $rooms->saveGroupRooms($g1, NULL, []));
    $this->assertSame([], $rooms->groupRooms($g1->id(), 'fixed'));
    $this->assertSame([], $rooms->groupRooms($g1->id(), 'forbidden'));
  }

  /**
   * Owner only; another planner's room is refused; cascades.
   */
  public function testAccessAndCascade(): void {
    $this->loadFixture();
    $rooms = $this->container->get(RoomConstraints::class);
    $g1 = $this->record('ancora_student_group', 'G1');
    $l1 = $this->record('ancora_room', 'L1');
    $l2 = $this->record('ancora_room', 'L2');
    $rooms->saveGroupRooms($g1, (int) $l1->id(), []);
    $rooms->saveTravelCosts($l1, [(int) $l2->id() => 3]);
    $count = fn (string $type) => (int) $this->container->get('entity_type.manager')->getStorage($type)->getQuery()->accessCheck(TRUE)->count()->execute();
    $this->assertSame(1, $count('ancora_group_room'));
    $this->assertSame(1, $count('ancora_travel_cost'));
    $other = $this->createUser(['view academic data', 'edit academic data']);
    $this->setCurrentUser($other);
    $this->assertSame(0, $count('ancora_group_room'));
    $this->assertSame(0, $count('ancora_travel_cost'));
    $foreign = $this->newRecord('ancora_room', ['label' => 'Foreign', 'code' => 'LF', 'capacity' => 10]);
    $foreign->save();
    $this->setCurrentUser($this->owner);
    $this->assertNotSame([], $rooms->saveGroupRooms($g1, (int) $foreign->id(), []));
    $this->assertNotSame([], $rooms->saveTravelCosts($l1, [(int) $foreign->id() => 2]));
    $this->assertSame([(int) $l1->id()], $rooms->groupRooms($g1->id(), 'fixed'), 'a refused save changes nothing');

    // Deleting room L2 (its assignments first) deletes its travel costs.
    $assignments = $this->container->get('entity_type.manager')->getStorage('ancora_room_assignment');
    $assignments->delete($assignments->loadByProperties(['room' => $l2->id()]));
    $l2->delete();
    $this->assertSame([], $rooms->travelCosts($l1->id()));
  }

  /**
   * The legacy priority upkeep of a template's rooms.
   */
  public function testRoomPriorities(): void {
    $this->loadFixture();
    $storage = $this->container->get('entity_type.manager')->getStorage('ancora_room_assignment');
    $l3 = $this->newRecord('ancora_room', ['label' => 'Room 3', 'code' => 'L3', 'capacity' => 50]);
    $l3->save();
    $template = $storage->load(array_key_first($storage->loadMultiple()))->get('template')->target_id;
    $priorities = function () use ($storage, $template): string {
      $storage->resetCache();
      $out = [];
      foreach ($storage->loadByProperties(['template' => $template]) as $a) {
        $out[$a->get('room')->entity->getCode()] = (int) $a->get('priority')->value;
      }
      ksort($out);
      return http_build_query($out, '', ' ');
    };
    // The fixture: L1 = 1, L2 = 2 for every template.
    $this->assertSame('L1=1 L2=2', $priorities());
    $values = ['plan' => 1, 'template' => $template, 'room' => $l3->id(), 'priority' => 3];
    $this->newRecord('ancora_room_assignment', $values)->save();
    // Delete L2 (priority 2, no other room of the template has it): L3
    // moves to 2.
    $l2 = $this->byCode('ancora_room', 'L2')->id();
    $storage->delete($storage->loadByProperties(['template' => $template, 'room' => $l2]));
    $this->assertSame('L1=1 L3=2', $priorities());
    // Update L3 to 5: priority 2 is still held by the other templates' L2,
    // so nothing moves (Q-07).
    $l3a = $storage->loadByProperties(['template' => $template, 'room' => $l3->id()]);
    reset($l3a)->set('priority', 5)->save();
    $this->assertSame('L1=1 L3=5', $priorities());
  }

  /**
   * The update installs the two entity types.
   */
  public function testUpdatePath(): void {
    $manager = $this->container->get('entity.definition_update_manager');
    foreach (['ancora_group_room', 'ancora_travel_cost'] as $entity_type_id) {
      $manager->uninstallEntityType($manager->getEntityType($entity_type_id));
    }
    $this->container->get('module_handler')->loadInclude('ancora_academic', 'install');
    $this->assertStringContainsString('ancora_group_room, ancora_travel_cost', ancora_academic_update_11007());
    $this->assertStringContainsString('none', ancora_academic_update_11007());
  }

}
