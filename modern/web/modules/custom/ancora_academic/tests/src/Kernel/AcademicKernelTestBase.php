<?php

declare(strict_types=1);

namespace Drupal\Tests\ancora_academic\Kernel;

use Drupal\ancora_academic\AcademicEntityInterface;
use Drupal\ancora_academic\AcademicEntityTypes;
use Drupal\ancora_academic\Entity\Plan;
use Drupal\ancora_academic\Fixture\SyntheticFixture;
use Drupal\Tests\ancora\Kernel\AncoraKernelTestBase;

/**
 * Base class of the academic Kernel tests.
 *
 * Extends the foundation's base class, which points SIMPLETEST_DB at the
 * disposable cluster's Unix socket when ANCORA_TEST_DB_SOCKET_DIR is set.
 */
abstract class AcademicKernelTestBase extends AncoraKernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'ancora_academic',
    'datetime',
    'system',
    'user',
    'views',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->installEntitySchema('user');
    foreach (AcademicEntityTypes::ids() as $entity_type_id) {
      $this->installEntitySchema($entity_type_id);
    }
    $this->installConfig(['ancora_academic']);
  }

  /**
   * Loads the synthetic fixture and returns its plan.
   */
  protected function loadFixture(bool $complete = TRUE): Plan {
    return $this->container->get(SyntheticFixture::class)->load($complete);
  }

  /**
   * Loads the record of a type with a code in a plan.
   */
  protected function byCode(string $entity_type_id, string $code, ?Plan $plan = NULL): AcademicEntityInterface {
    $properties = ['code' => $code];
    if ($entity_type_id !== 'ancora_plan' && $plan !== NULL) {
      $properties['plan'] = $plan->id();
    }
    $entities = $this->container->get('entity_type.manager')->getStorage($entity_type_id)->loadByProperties($properties);
    $this->assertCount(1, $entities, "one $entity_type_id with code $code");
    $entity = reset($entities);
    $this->assertInstanceOf(AcademicEntityInterface::class, $entity);
    return $entity;
  }

  /**
   * Creates an unsaved record.
   *
   * @param string $entity_type_id
   *   The entity type.
   * @param array<string, mixed> $values
   *   The field values.
   */
  protected function newRecord(string $entity_type_id, array $values): AcademicEntityInterface {
    $entity = $this->container->get('entity_type.manager')->getStorage($entity_type_id)->create($values);
    $this->assertInstanceOf(AcademicEntityInterface::class, $entity);
    return $entity;
  }

  /**
   * Returns the fields with violations of a record, sorted and unique.
   *
   * @return list<string>
   *   The field names (first segment of each property path).
   */
  protected function violationFields(AcademicEntityInterface $entity): array {
    $fields = [];
    foreach ($entity->validate() as $violation) {
      $fields[] = explode('.', $violation->getPropertyPath())[0];
    }
    $fields = array_values(array_unique($fields));
    sort($fields);
    return $fields;
  }

  /**
   * Counts the records of a type.
   */
  protected function recordCount(string $entity_type_id): int {
    return (int) $this->container->get('entity_type.manager')->getStorage($entity_type_id)->getQuery()
      ->accessCheck(FALSE)->count()->execute();
  }

}
