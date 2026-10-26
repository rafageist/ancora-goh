<?php

declare(strict_types=1);

namespace Drupal\Tests\ancora_academic\Kernel;

use Drupal\ancora_academic\AcademicAccessControlHandler;
use Drupal\ancora_academic\AcademicEntityTypes;
use Drupal\ancora_academic\Hook\AcademicQueryAccess;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;

/**
 * Tests the creator rule of the academic entities (legacy "creador").
 *
 * Legacy (legacy-parity-inventory.md PAR-001, PAR-002): each user sees and
 * edits only the records they created and the records of their plans; the
 * timetable explorer lists every user's teachers and rooms (quirk Q-23).
 */
#[CoversClass(AcademicAccessControlHandler::class)]
#[CoversClass(AcademicQueryAccess::class)]
#[Group('ancora')]
final class AcademicAccessTest extends AcademicKernelTestBase {

  /**
   * Owner, other user, viewer, no permission and administrator, per type.
   */
  public function testCreatorRule(): void {
    $this->loadFixture();
    $plain = $this->createUser([]);
    $viewer = $this->createUser(['view academic data']);
    $other = $this->createUser(['view academic data', 'edit academic data']);
    $administrator = $this->createUser(['administer academic data']);

    $entity_type_manager = $this->container->get('entity_type.manager');
    foreach (AcademicEntityTypes::ids() as $entity_type_id) {
      $ids = $entity_type_manager->getStorage($entity_type_id)->getQuery()->accessCheck(FALSE)->range(0, 1)->execute();
      $entity = $entity_type_manager->getStorage($entity_type_id)->load(reset($ids));
      $this->assertNotNull($entity, $entity_type_id);
      $this->assertSame((int) $this->owner->id(), $entity->getCreatorId(), "$entity_type_id creator");
      $handler = $entity_type_manager->getAccessControlHandler($entity_type_id);
      $visible_to_all = in_array($entity_type_id, AcademicAccessControlHandler::VISIBLE_TO_ALL, TRUE);

      foreach (['view', 'update', 'delete'] as $operation) {
        $this->assertTrue($entity->access($operation, $this->owner), "$entity_type_id $operation, owner");
        $this->assertFalse($entity->access($operation, $plain), "$entity_type_id $operation, no permission");
        $this->assertTrue($entity->access($operation, $administrator), "$entity_type_id $operation, administrator");
      }
      $this->assertSame($visible_to_all, $entity->access('view', $other), "$entity_type_id view, another editor");
      $this->assertFalse($entity->access('update', $other), "$entity_type_id update, another editor");
      $this->assertFalse($entity->access('delete', $other), "$entity_type_id delete, another editor");
      $this->assertSame($visible_to_all, $entity->access('view', $viewer), "$entity_type_id view, another viewer");
      $this->assertFalse($entity->access('update', $viewer), "$entity_type_id update, viewer");

      // Plans too: every data entry user creates their own.
      $this->assertTrue($handler->createAccess(NULL, $this->owner), "$entity_type_id create, editor");
      $this->assertTrue($handler->createAccess(NULL, $other), "$entity_type_id create, another editor");
      $this->assertFalse($handler->createAccess(NULL, $viewer), "$entity_type_id create, viewer");
      $this->assertFalse($handler->createAccess(NULL, $plain), "$entity_type_id create, no permission");
      $this->assertTrue($handler->createAccess(NULL, $administrator), "$entity_type_id create, administrator");
    }
  }

  /**
   * Access-checked queries return the current user's records only.
   */
  public function testQueriesAreFilteredByCreator(): void {
    $this->loadFixture();
    $other = $this->createUser(['view academic data', 'edit academic data']);
    $administrator = $this->createUser(['administer academic data']);
    $plain = $this->createUser([]);
    $entity_type_manager = $this->container->get('entity_type.manager');
    $count = fn (string $entity_type_id): int => (int) $entity_type_manager->getStorage($entity_type_id)->getQuery()->accessCheck(TRUE)->count()->execute();

    foreach (AcademicEntityTypes::ids() as $entity_type_id) {
      $all = $this->recordCount($entity_type_id);
      $this->setCurrentUser($this->owner);
      $this->assertSame($all, $count($entity_type_id), "$entity_type_id, owner");
      $this->setCurrentUser($other);
      $this->assertSame(0, $count($entity_type_id), "$entity_type_id, another user");
      $this->setCurrentUser($plain);
      $this->assertSame(0, $count($entity_type_id), "$entity_type_id, no permission");
      $this->setCurrentUser($administrator);
      $this->assertSame($all, $count($entity_type_id), "$entity_type_id, administrator");
    }

    // The other user's own teacher is listed for them only.
    $this->setCurrentUser($other);
    $teacher = $this->newRecord('ancora_teacher', ['label' => 'Other teacher', 'code' => 'TA']);
    $this->assertSame([], $this->violationFields($teacher));
    $teacher->save();
    $this->assertSame((int) $other->id(), $teacher->getCreatorId());
    $this->assertSame(1, $count('ancora_teacher'));
    $this->setCurrentUser($this->owner);
    $this->assertSame(2, $count('ancora_teacher'));
  }

  /**
   * Only an administrator may change the owner field.
   */
  public function testOwnerFieldAccess(): void {
    $plan = $this->loadFixture();
    $administrator = $this->createUser(['administer academic data']);
    $this->assertFalse($plan->get('uid')->access('edit', $this->owner));
    $this->assertTrue($plan->get('uid')->access('edit', $administrator));
  }

}
