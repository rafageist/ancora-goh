<?php

declare(strict_types=1);

namespace Drupal\Tests\ancora_academic\Kernel;

use Drupal\ancora_academic\AcademicAccessControlHandler;
use Drupal\ancora_academic\AcademicEntityTypes;
use Drupal\Tests\user\Traits\UserCreationTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;

/**
 * Tests the permissions of the academic entities.
 */
#[CoversClass(AcademicAccessControlHandler::class)]
#[Group('ancora')]
final class AcademicAccessTest extends AcademicKernelTestBase {

  use UserCreationTrait;

  /**
   * View, edit and administer permissions apply to every type.
   */
  public function testPermissions(): void {
    $this->loadFixture();
    // User 1 is created first so that the next accounts are not superusers.
    $this->createUser();
    $plain = $this->createUser([]);
    $viewer = $this->createUser(['view academic data']);
    $editor = $this->createUser(['view academic data', 'edit academic data']);
    $administrator = $this->createUser(['administer academic data']);

    $entity_type_manager = $this->container->get('entity_type.manager');
    foreach (AcademicEntityTypes::ids() as $entity_type_id) {
      $ids = $entity_type_manager->getStorage($entity_type_id)->getQuery()->accessCheck(FALSE)->range(0, 1)->execute();
      $entity = $entity_type_manager->getStorage($entity_type_id)->load(reset($ids));
      $this->assertNotNull($entity, $entity_type_id);
      $handler = $entity_type_manager->getAccessControlHandler($entity_type_id);
      $is_plan = $entity_type_id === 'ancora_plan';

      foreach (['view', 'update', 'delete'] as $operation) {
        $this->assertFalse($entity->access($operation, $plain), "$entity_type_id $operation, no permission");
      }
      $this->assertFalse($handler->createAccess(NULL, $plain), "$entity_type_id create, no permission");

      $this->assertTrue($entity->access('view', $viewer), "$entity_type_id view, viewer");
      $this->assertFalse($entity->access('update', $viewer), "$entity_type_id update, viewer");
      $this->assertFalse($entity->access('delete', $viewer), "$entity_type_id delete, viewer");
      $this->assertFalse($handler->createAccess(NULL, $viewer), "$entity_type_id create, viewer");

      $this->assertTrue($entity->access('view', $editor), "$entity_type_id view, editor");
      $this->assertTrue($entity->access('update', $editor), "$entity_type_id update, editor");
      $this->assertSame(!$is_plan, $entity->access('delete', $editor), "$entity_type_id delete, editor");
      $this->assertSame(!$is_plan, $handler->createAccess(NULL, $editor), "$entity_type_id create, editor");

      foreach (['view', 'update', 'delete'] as $operation) {
        $this->assertTrue($entity->access($operation, $administrator), "$entity_type_id $operation, administrator");
      }
      $this->assertTrue($handler->createAccess(NULL, $administrator), "$entity_type_id create, administrator");
    }
  }

}
