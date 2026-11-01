<?php

declare(strict_types=1);

namespace Drupal\ancora_engine;

use Drupal\Core\Site\Settings;

/**
 * Server-side settings of the engine worker.
 *
 * Nothing here comes from job data or user input. The source is
 * $settings['ancora_engine'] in the site's settings.php:
 *
 * @code
 * $settings['ancora_engine'] = [
 *   'database' => [
 *     'host' => '/run/postgresql',   // host name or Unix socket directory
 *     'port' => 5432,
 *     'database' => 'tt_example_engine',
 *     'username' => 'tt_example_engine',
 *     'password_file' => '/run/credentials/ancora/engine-db-password',
 *     'sslmode' => 'prefer',
 *   ],
 *   'time_limit' => 120,         // default limit of a job, seconds
 *   'max_time_limit' => 120,     // largest limit a job may ask for (plan)
 *   'heartbeat_interval' => 5,   // seconds
 *   'stale_after' => 60,         // seconds without heartbeat
 *   'max_attempts' => 2,         // claims of a job (one requeue)
 *   'cancel_grace' => 15,        // seconds from soft to hard cancellation
 *   'mode' => 'two_database',    // or 'workspace' (#95)
 *   'workspace_retention' => 0,  // seconds a closed job workspace is kept
 * ];
 * @endcode
 *
 * Modes:
 * - two_database (the default, the deployed release): "database" is the
 *   site's own engine database; the run syncs the site into it and the
 *   placements stay there (EngineClient).
 * - workspace (#93, #95): "database" is the SITE's own database, where the
 *   unchanged engine is installed (dev/workspace/install.sh), and the role is
 *   the site's engine worker. Every run gets a job-scoped workspace schema;
 *   its output is published as Drupal entities (WorkspaceClient,
 *   JobOutputInterface). No engine database exists.
 *
 * When $settings['ancora_engine'] is not set, the same values are read from
 * the environment: ANCORA_ENGINE_DB_HOST, ANCORA_ENGINE_DB_PORT,
 * ANCORA_ENGINE_DB_NAME, ANCORA_ENGINE_DB_USER, ANCORA_ENGINE_DB_PASSWORD_FILE,
 * ANCORA_ENGINE_DB_SSLMODE, ANCORA_ENGINE_TIME_LIMIT,
 * ANCORA_ENGINE_MAX_TIME_LIMIT, ANCORA_ENGINE_HEARTBEAT_INTERVAL,
 * ANCORA_ENGINE_STALE_AFTER, ANCORA_ENGINE_MAX_ATTEMPTS and
 * ANCORA_ENGINE_CANCEL_GRACE, ANCORA_ENGINE_MODE and
 * ANCORA_ENGINE_WORKSPACE_RETENTION
 * (prefix: $settings['ancora_engine_env_prefix'],
 * default ANCORA_, the convention of settings/ancora.settings.php).
 */
final class EngineSettings {

  /**
   * Defaults: the Free plan limit (#55) and the recovery policy.
   */
  private const DEFAULTS = [
    'time_limit' => 120,
    'max_time_limit' => 120,
    'heartbeat_interval' => 5,
    'stale_after' => 60,
    'max_attempts' => 2,
    'cancel_grace' => 15,
  ];

  /**
   * The resolved settings.
   */
  private ?array $values = NULL;

  public function __construct(
    private readonly Settings $settings,
  ) {}

  /**
   * Whether an engine database is configured.
   */
  public function isConfigured(): bool {
    $db = $this->all()['database'];
    return ($db['database'] ?? '') !== '' && ($db['username'] ?? '') !== '';
  }

  /**
   * The engine database connection parameters, password included.
   *
   * @return array
   *   host, port, database, username, password, sslmode.
   *
   * @throws \RuntimeException
   *   When the database is not configured or the password file is unreadable.
   */
  public function database(): array {
    if (!$this->isConfigured()) {
      throw new \RuntimeException('The engine database of this site is not configured.');
    }
    $db = $this->all()['database'] + [
      'host' => 'localhost',
      'port' => 5432,
      'password' => '',
      'sslmode' => '',
    ];
    if (!empty($db['password_file'])) {
      $value = is_file($db['password_file']) && is_readable($db['password_file']) ? file_get_contents($db['password_file']) : FALSE;
      if ($value === FALSE || trim($value) === '') {
        throw new \RuntimeException('The engine database password file is missing, unreadable or empty.');
      }
      $db['password'] = rtrim($value, "\r\n");
    }
    unset($db['password_file']);
    return $db;
  }

  /**
   * Default time limit of a job, in seconds.
   */
  public function timeLimit(): int {
    return min($this->int('time_limit'), $this->maxTimeLimit());
  }

  /**
   * Largest time limit a job may ask for, in seconds.
   */
  public function maxTimeLimit(): int {
    return $this->int('max_time_limit');
  }

  /**
   * Seconds between heartbeats of a running job.
   */
  public function heartbeatInterval(): int {
    return $this->int('heartbeat_interval');
  }

  /**
   * Seconds without heartbeat after which a running job is stale.
   */
  public function staleAfter(): int {
    return max($this->int('stale_after'), 2 * $this->heartbeatInterval());
  }

  /**
   * Number of claims of a job before a lost worker fails it.
   */
  public function maxAttempts(): int {
    return $this->int('max_attempts');
  }

  /**
   * Seconds between a soft cancellation and the hard one.
   */
  public function cancelGrace(): int {
    return $this->int('cancel_grace');
  }

  /**
   * The run mode: 'two_database' (default) or 'workspace'.
   */
  public function mode(): string {
    $all = $this->all();
    return ($all['mode'] ?? 'two_database') === 'workspace' ? 'workspace' : 'two_database';
  }

  /**
   * Whether runs use a job-scoped workspace in the site database.
   */
  public function isWorkspace(): bool {
    return $this->mode() === 'workspace';
  }

  /**
   * Seconds a closed job workspace is kept for diagnosis (0: dropped).
   */
  public function workspaceRetention(): int {
    return max(0, (int) ($this->all()['workspace_retention'] ?? 0));
  }

  /**
   * A positive integer setting.
   */
  private function int(string $key): int {
    $value = (int) ($this->all()[$key] ?? self::DEFAULTS[$key]);
    return $value > 0 ? $value : self::DEFAULTS[$key];
  }

  /**
   * Resolves the settings once.
   */
  private function all(): array {
    if ($this->values !== NULL) {
      return $this->values;
    }
    $configured = $this->settings->get('ancora_engine');
    if (is_array($configured)) {
      $configured += ['database' => []];
      return $this->values = $configured + self::DEFAULTS;
    }
    $prefix = (string) $this->settings->get('ancora_engine_env_prefix', 'ANCORA_');
    $env = static function (string $name) use ($prefix): ?string {
      $value = getenv($prefix . 'ENGINE_' . $name);
      return ($value === FALSE || $value === '') ? NULL : $value;
    };
    $values = [
      'database' => array_filter([
        'host' => $env('DB_HOST'),
        'port' => $env('DB_PORT'),
        'database' => $env('DB_NAME'),
        'username' => $env('DB_USER'),
        'password_file' => $env('DB_PASSWORD_FILE'),
        'sslmode' => $env('DB_SSLMODE'),
      ], static fn ($value) => $value !== NULL),
    ];
    foreach (array_keys(self::DEFAULTS) as $key) {
      $values[$key] = $env(strtoupper($key)) ?? self::DEFAULTS[$key];
    }
    $values['mode'] = $env('MODE') ?? 'two_database';
    $values['workspace_retention'] = $env('WORKSPACE_RETENTION') ?? 0;
    return $this->values = $values;
  }

}
