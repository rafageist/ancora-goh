<?php

declare(strict_types=1);

namespace Drupal\Tests\ancora_engine\Kernel;

use Drupal\ancora_engine\EngineSettings;
use Drupal\ancora_engine\Job\JobOptions;
use Drupal\ancora_engine\Job\JobScope;
use Drupal\Core\Site\Settings;
use Drupal\Tests\ancora\Kernel\AncoraKernelTestBase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;

/**
 * Validation of generar() options, scopes and engine settings.
 */
#[Group('ancora_engine')]
final class JobValidationTest extends AncoraKernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['system', 'user', 'ancora_engine'];

  /**
   * Valid options and their bound parameters.
   */
  public function testValidOptions(): void {
    $this->assertSame(['1', 'false', '0', 'false', 'false'], JobOptions::fromArray([])->toParameters());
    $options = JobOptions::fromArray([
      'forma' => '2',
      'parche' => '1',
      'compactar' => '10',
      'parche1' => 'true',
      'buscalugar' => TRUE,
    ]);
    $this->assertSame(['2', 'true', '10', 'true', 'true'], $options->toParameters());
    $this->assertSame(['forma' => 2, 'parche' => TRUE, 'compactar' => 10, 'parche1' => TRUE, 'buscalugar' => TRUE], $options->toArray());
    $this->assertEquals($options, JobOptions::fromArray($options->toArray()));
  }

  /**
   * Invalid option sets.
   */
  public static function invalidOptions(): array {
    return [
      'forma 0' => [['forma' => 0]],
      'forma 3' => [['forma' => '3']],
      'forma text' => [['forma' => '1; DROP TABLE actividad']],
      'forma float' => [['forma' => 1.5]],
      'compactar negative' => [['compactar' => -1]],
      'compactar 11' => [['compactar' => 11]],
      'compactar text' => [['compactar' => 'ten']],
      'parche yes' => [['parche' => 'yes']],
      'parche 2' => [['parche' => 2]],
      'parche1 null' => [['parche1' => NULL]],
      'buscalugar array' => [['buscalugar' => [TRUE]]],
      'unknown key' => [['statement_timeout' => 0]],
    ];
  }

  /**
   * Invalid options are refused.
   */
  #[DataProvider('invalidOptions')]
  public function testInvalidOptions(array $values): void {
    $this->expectException(\InvalidArgumentException::class);
    JobOptions::fromArray($values);
  }

  /**
   * Scope ids: non-empty, bounded, no control characters.
   */
  public function testScope(): void {
    $this->assertSame(['E', 'N', 'PER'], (new JobScope('E', 'N', 'PER'))->toParameters());
    foreach ([['', 'N', 'PER'], ['E ', 'N', 'PER'], ['E', "N\n", 'PER'], ['E', 'N', str_repeat('x', 256)]] as $bad) {
      try {
        new JobScope(...$bad);
        $this->fail('Accepted an invalid scope: ' . json_encode($bad));
      }
      catch (\InvalidArgumentException) {
        $this->addToAssertionCount(1);
      }
    }
  }

  /**
   * Engine settings: defaults, bounds and the password file.
   */
  public function testSettings(): void {
    $password_file = tempnam(sys_get_temp_dir(), 'ancora-engine-test');
    file_put_contents($password_file, "s3cret\n");
    try {
      $settings = new EngineSettings(new Settings([
        'ancora_engine' => [
          'database' => [
            'host' => '/run/postgresql',
            'database' => 'tt_a_engine',
            'username' => 'tt_a_engine',
            'password_file' => $password_file,
          ],
          'time_limit' => 500,
          'stale_after' => 1,
        ],
      ]));
      $this->assertTrue($settings->isConfigured());
      $db = $settings->database();
      $this->assertSame('s3cret', $db['password']);
      $this->assertArrayNotHasKey('password_file', $db);
      $this->assertSame(5432, $db['port']);
      // The default limit never exceeds the plan maximum (Free: 120 s).
      $this->assertSame(120, $settings->maxTimeLimit());
      $this->assertSame(120, $settings->timeLimit());
      // A job is stale only after at least two missed heartbeats.
      $this->assertSame(10, $settings->staleAfter());
      $this->assertSame(2, $settings->maxAttempts());
    }
    finally {
      unlink($password_file);
    }

    $missing = new EngineSettings(new Settings([
      'ancora_engine' => [
        'database' => ['database' => 'x', 'username' => 'y', 'password_file' => '/nonexistent'],
      ],
    ]));
    $this->expectException(\RuntimeException::class);
    $missing->database();
  }

  /**
   * Without a configured database the worker refuses to connect.
   */
  public function testUnconfigured(): void {
    $settings = new EngineSettings(new Settings(['ancora_engine' => []]));
    $this->assertFalse($settings->isConfigured());
    $this->expectException(\RuntimeException::class);
    $settings->database();
  }

}
