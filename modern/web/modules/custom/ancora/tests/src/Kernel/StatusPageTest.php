<?php

declare(strict_types=1);

namespace Drupal\Tests\ancora\Kernel;

use Drupal\ancora\Controller\StatusController;
use Drupal\ancora\Entity\AcademicTerm;
use Drupal\Tests\user\Traits\UserCreationTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;

/**
 * Tests the status route permission and the status page output.
 */
#[CoversClass(StatusController::class)]
#[Group('ancora')]
final class StatusPageTest extends AncoraKernelTestBase {

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
  }

  /**
   * Only accounts with "view ancora status" may open the route.
   */
  public function testRouteAccess(): void {
    $access_manager = $this->container->get('access_manager');
    $this->createUser();
    $plain = $this->createUser([]);
    $operator = $this->createUser(['view ancora status']);

    $this->assertFalse($access_manager->checkNamedRoute('ancora.status', [], $plain));
    $this->assertTrue($access_manager->checkNamedRoute('ancora.status', [], $operator));
  }

  /**
   * The page reports the core version, the database and the term count.
   */
  public function testOutput(): void {
    AcademicTerm::create([
      'label' => 'Term',
      'code' => 'T1',
      'starts_on' => '2026-09-01',
      'ends_on' => '2026-12-18',
    ])->save();

    $build = StatusController::create($this->container)->status();
    $html = (string) $this->container->get('renderer')->renderInIsolation($build);

    $this->assertStringContainsString(\Drupal::VERSION, $html);
    $this->assertStringContainsString('pgsql', $html);
    $this->assertMatchesRegularExpression('#<dt>Academic terms</dt>\s*<dd>1</dd>#', $html);
    $this->assertContains('ancora_academic_term_list', $build['#cache']['tags']);
  }

}
