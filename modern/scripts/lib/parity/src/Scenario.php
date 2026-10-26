<?php

declare(strict_types=1);

namespace Ancora\Parity;

/**
 * A validated parity scenario (modern/tests/parity/scenarios/*.json).
 *
 * Shared by the legacy driver, the Drupal driver, the normalizer and the
 * comparer of the differential parity harness (scripts/parity-diff.sh); the
 * format is documented in docs/modernization/legacy-parity-harness.md. In
 * short: users; records in data entry order, each entered by one user
 * ("as"); scheduling runs; visibility checks. Records reference earlier
 * records by key. Owned records (plans and the catalogues) belong to the
 * user who enters them, as the legacy "creador" does; plan-scoped records
 * (periods, groups, templates, assignments) belong to a plan.
 */
final class Scenario {

  public const SCHEMA = 'ancora.parity.scenario.v1';

  /**
   * Record types and the fields each one references, by referenced type.
   */
  public const TYPES = [
    'plan' => [],
    'period' => ['plan' => 'plan'],
    'activity_type' => [],
    'specialty' => [],
    'level' => [],
    'cohort' => ['specialty' => 'specialty', 'level' => 'level'],
    'student_group' => [
      'plan' => 'plan',
      'activity_type' => 'activity_type',
      'specialty' => 'specialty',
      'level' => 'level',
      'cohorts' => 'cohort',
    ],
    'subject' => ['specialty' => 'specialty', 'level' => 'level'],
    'activity_template' => [
      'subject' => 'subject',
      'period' => 'period',
      'activity_type' => 'activity_type',
    ],
    'teacher' => [],
    'teacher_assignment' => [
      'template' => 'activity_template',
      'teacher' => 'teacher',
      'groups' => 'student_group',
    ],
    'room' => [],
    'room_assignment' => ['template' => 'activity_template', 'room' => 'room'],
  ];

  /**
   * Types owned by the user who enters them (legacy column "creador").
   */
  public const OWNED = [
    'plan', 'activity_type', 'specialty', 'level', 'cohort', 'subject',
    'teacher', 'room',
  ];

  /**
   * The decoded scenario.
   *
   * @var array<string, mixed>
   */
  public readonly array $data;

  /**
   * Records by key.
   *
   * @var array<string, array<string, mixed>>
   */
  public readonly array $records;

  /**
   * Users by key.
   *
   * @var array<string, array<string, mixed>>
   */
  public readonly array $users;

  /**
   * Loads and validates a scenario file.
   */
  public static function load(string $file): self {
    $json = file_get_contents($file);
    if ($json === FALSE) {
      throw new \RuntimeException("cannot read $file");
    }
    return new self(json_decode($json, TRUE, 64, JSON_THROW_ON_ERROR));
  }

  /**
   * Validates the decoded scenario.
   *
   * @param array<string, mixed> $data
   *   The decoded JSON.
   */
  public function __construct(array $data) {
    if (($data['schema'] ?? NULL) !== self::SCHEMA) {
      throw new \InvalidArgumentException('unsupported scenario schema');
    }
    $this->data = $data;
    $users = [];
    foreach ($data['users'] as $user) {
      if (!preg_match('/^[a-z][a-z0-9_]{0,30}$/', $user['key'])) {
        throw new \InvalidArgumentException("invalid user key {$user['key']}");
      }
      $users[$user['key']] = $user;
    }
    $this->users = $users;
    $records = [];
    foreach ($data['records'] as $record) {
      $key = $record['key'] ?? '';
      $type = $record['type'] ?? '';
      if (!isset(self::TYPES[$type])) {
        throw new \InvalidArgumentException("record $key: unknown type $type");
      }
      if ($key === '' || isset($records[$key])) {
        throw new \InvalidArgumentException("record key '$key' is empty or repeated");
      }
      if (!isset($users[$record['as'] ?? ''])) {
        throw new \InvalidArgumentException("record $key: unknown user");
      }
      foreach (self::TYPES[$type] as $field => $target) {
        if (!isset($record[$field])) {
          continue;
        }
        foreach ((array) $record[$field] as $ref) {
          if (($records[$ref]['type'] ?? NULL) !== $target) {
            throw new \InvalidArgumentException("record $key: $field '$ref' is not an earlier $target");
          }
        }
      }
      $records[$key] = $record;
    }
    $this->records = $records;
    $seen = [];
    foreach ($records as $key => $record) {
      $natural = implode("\x1F", $this->naturalKey($key));
      if (isset($seen[$natural])) {
        throw new \InvalidArgumentException("records {$seen[$natural]} and $key have the same natural key (label, owner or plan)");
      }
      $seen[$natural] = $key;
    }
  }

  /**
   * The scenario id.
   */
  public function id(): string {
    return (string) $this->data['id'];
  }

  /**
   * The code of a record in Drupal: "code", or the key.
   */
  public function code(string $key): string {
    return (string) ($this->records[$key]['code'] ?? $key);
  }

  /**
   * The plan of a plan-scoped record (a template's plan is its period's).
   */
  public function planOf(string $key): ?string {
    $record = $this->records[$key];
    return match ($record['type']) {
      'plan' => $key,
      'period', 'student_group' => $record['plan'],
      'activity_template' => $this->planOf($record['period']),
      'teacher_assignment', 'room_assignment' => $this->planOf($record['template']),
      default => NULL,
    };
  }

  /**
   * The owner (user key) of a record: the creator, or the plan's creator.
   */
  public function ownerOf(string $key): string {
    $record = $this->records[$key];
    if (in_array($record['type'], self::OWNED, TRUE)) {
      return $record['as'];
    }
    return $this->records[$this->planOf($key)]['as'];
  }

  /**
   * The natural key of a record: what identifies it in both databases.
   *
   * Legacy ids are generated (random replicas); Drupal engine ids are
   * derived from codes. Neither is comparable, so rows are matched on the
   * natural key: the owner (or plan) and the name, as a user tells records
   * apart. Labels must therefore be unique per owner (or plan) and type.
   *
   * @return list<string>
   *   The natural key parts.
   */
  public function naturalKey(string $key): array {
    $r = $this->records[$key];
    return match ($r['type']) {
      'period', 'student_group' => [$r['type'], $r['plan'], $r['label']],
      'activity_template' => [$r['type'], $r['subject'], $r['period'], (string) $r['sequence']],
      'teacher_assignment' => [$r['type'], $r['template'], $r['teacher']],
      'room_assignment' => [$r['type'], $r['template'], $r['room']],
      default => [$r['type'], $r['as'], $r['label']],
    };
  }

  /**
   * Record keys by natural key (parts joined with \x1F).
   *
   * @return array<string, string>
   *   The index.
   */
  public function naturalIndex(): array {
    $index = [];
    foreach (array_keys($this->records) as $key) {
      $index[implode("\x1F", $this->naturalKey($key))] = $key;
    }
    return $index;
  }

  /**
   * Records of one type, in scenario order.
   *
   * @return array<string, array<string, mixed>>
   *   Records by key.
   */
  public function ofType(string $type): array {
    return array_filter($this->records, static fn (array $r): bool => $r['type'] === $type);
  }

  /**
   * The scheduling runs.
   *
   * @return list<array<string, mixed>>
   *   The runs.
   */
  public function runs(): array {
    return $this->data['runs'] ?? [];
  }

  /**
   * The visibility checks.
   *
   * @return list<array<string, mixed>>
   *   The checks.
   */
  public function visibility(): array {
    return $this->data['visibility'] ?? [];
  }

}
