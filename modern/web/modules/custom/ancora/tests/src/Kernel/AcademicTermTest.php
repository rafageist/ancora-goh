<?php

declare(strict_types=1);

namespace Drupal\Tests\ancora\Kernel;

use Drupal\ancora\Entity\AcademicTerm;
use Drupal\Tests\user\Traits\UserCreationTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;

/**
 * Tests the academic term entity: storage, validation and access.
 */
#[CoversClass(AcademicTerm::class)]
#[Group('ancora')]
final class AcademicTermTest extends AncoraKernelTestBase {

  use UserCreationTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'ancora',
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
    $this->installEntitySchema('ancora_academic_term');
    $this->installConfig(['ancora']);
  }

  /**
   * Creates an unsaved term with the given values over valid defaults.
   */
  private function term(array $values = []): AcademicTerm {
    return AcademicTerm::create($values + [
      'label' => 'First semester 2026',
      'code' => '2026-S1',
      'starts_on' => '2026-02-02',
      'ends_on' => '2026-06-30',
    ]);
  }

  /**
   * A valid term is stored and loaded with its values.
   */
  public function testCreateAndLoad(): void {
    $term = $this->term();
    $this->assertCount(0, $term->validate());
    $term->save();

    $loaded = AcademicTerm::load($term->id());
    $this->assertInstanceOf(AcademicTerm::class, $loaded);
    $this->assertSame('First semester 2026', $loaded->label());
    $this->assertSame('2026-S1', $loaded->getCode());
    $this->assertSame('2026-02-02', $loaded->getStartDate());
    $this->assertSame('2026-06-30', $loaded->getEndDate());
    $this->assertNotEmpty($loaded->getChangedTime());
  }

  /**
   * The last day must not be before the first day.
   */
  public function testEndBeforeStartIsRejected(): void {
    $violations = $this->term(['ends_on' => '2026-01-31'])->validate();
    $this->assertCount(1, $violations);
    $this->assertSame('ends_on', $violations[0]->getPropertyPath());

    // A one-day term is valid.
    $this->assertCount(0, $this->term(['ends_on' => '2026-02-02'])->validate());
  }

  /**
   * Codes are unique and required fields are enforced.
   */
  public function testCodeIsUniqueAndFieldsAreRequired(): void {
    $this->term()->save();
    $violations = $this->term(['label' => 'Duplicate'])->validate();
    $this->assertCount(1, $violations);
    $this->assertSame('code', $violations[0]->getPropertyPath());

    $paths = [];
    foreach (AcademicTerm::create([])->validate() as $violation) {
      $paths[] = $violation->getPropertyPath();
    }
    sort($paths);
    $this->assertSame(['code', 'ends_on', 'label', 'starts_on'], $paths);
  }

  /**
   * Viewing and administering need separate permissions.
   */
  public function testAccess(): void {
    $term = $this->term();
    $term->save();

    // User 1 is created first so that the next accounts are not superusers.
    $this->createUser();
    $plain = $this->createUser([]);
    $viewer = $this->createUser(['view ancora academic terms']);
    $administrator = $this->createUser(['administer ancora academic terms']);

    $this->assertFalse($term->access('view', $plain));
    $this->assertTrue($term->access('view', $viewer));
    $this->assertFalse($term->access('update', $viewer));
    $this->assertFalse($term->access('delete', $viewer));
    $this->assertTrue($term->access('update', $administrator));
    $this->assertTrue($term->access('delete', $administrator));

    $access_handler = $this->container->get('entity_type.manager')->getAccessControlHandler('ancora_academic_term');
    $this->assertFalse($access_handler->createAccess(NULL, $viewer));
    $this->assertTrue($access_handler->createAccess(NULL, $administrator));
  }

}
