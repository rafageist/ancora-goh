<?php

declare(strict_types=1);

namespace Drupal\ancora_engine\Job;

/**
 * The validated options of generar().
 *
 * The values and their meaning come from the legacy generator window
 * (www/js/app/client/wins/ancoragoh_client_wins_generador.js):
 * - forma: prioritization, 1 = order of the activity classes, 2 = number of
 *   activities of the subjects.
 * - parche: group activities by class ("agrupar").
 * - compactar: compaction towards the start of the period, 0 to 10.
 * - parche1: maximize the combination of activities per day ("maximizar").
 * - buscalugar: TRUE = most used place, FALSE = nearest place.
 */
final class JobOptions {

  private const KEYS = ['forma', 'parche', 'compactar', 'parche1', 'buscalugar'];

  public function __construct(
    public readonly int $forma = 1,
    public readonly bool $parche = FALSE,
    public readonly int $compactar = 0,
    public readonly bool $parche1 = FALSE,
    public readonly bool $buscalugar = FALSE,
  ) {
    if (!in_array($forma, [1, 2], TRUE)) {
      throw new \InvalidArgumentException('forma must be 1 or 2.');
    }
    if ($compactar < 0 || $compactar > 10) {
      throw new \InvalidArgumentException('compactar must be an integer from 0 to 10.');
    }
  }

  /**
   * Builds options from untrusted input, refusing anything unexpected.
   *
   * @param array $values
   *   Keys among forma, parche, compactar, parche1, buscalugar. Integers may
   *   be given as decimal strings; booleans as bool, 0/1, "0"/"1",
   *   "true"/"false".
   *
   * @throws \InvalidArgumentException
   *   On an unknown key or an invalid value.
   */
  public static function fromArray(array $values): self {
    $unknown = array_diff(array_map('strval', array_keys($values)), self::KEYS);
    if ($unknown !== []) {
      throw new \InvalidArgumentException(sprintf('Unknown option(s): %s.', implode(', ', $unknown)));
    }
    // A key given with NULL is invalid, not a default.
    $values += ['forma' => 1, 'parche' => FALSE, 'compactar' => 0, 'parche1' => FALSE, 'buscalugar' => FALSE];
    return new self(
      self::toInt($values['forma'], 'forma'),
      self::toBool($values['parche'], 'parche'),
      self::toInt($values['compactar'], 'compactar'),
      self::toBool($values['parche1'], 'parche1'),
      self::toBool($values['buscalugar'], 'buscalugar'),
    );
  }

  /**
   * The options as stored in the job (JSON object).
   */
  public function toArray(): array {
    return [
      'forma' => $this->forma,
      'parche' => $this->parche,
      'compactar' => $this->compactar,
      'parche1' => $this->parche1,
      'buscalugar' => $this->buscalugar,
    ];
  }

  /**
   * The options as generar() parameters 4 to 8 (bound text values).
   *
   * @return string[]
   *   forma, parche, compactar, parche1, buscalugar.
   */
  public function toParameters(): array {
    $bool = static fn (bool $value): string => $value ? 'true' : 'false';
    return [
      (string) $this->forma,
      $bool($this->parche),
      (string) $this->compactar,
      $bool($this->parche1),
      $bool($this->buscalugar),
    ];
  }

  /**
   * Converts an integer option.
   */
  private static function toInt(mixed $value, string $name): int {
    if (is_int($value)) {
      return $value;
    }
    if (is_string($value) && preg_match('/^-?[0-9]{1,9}$/', $value)) {
      return (int) $value;
    }
    throw new \InvalidArgumentException(sprintf('%s must be an integer.', $name));
  }

  /**
   * Converts a boolean option.
   */
  private static function toBool(mixed $value, string $name): bool {
    if (is_bool($value)) {
      return $value;
    }
    if ($value === 0 || $value === 1) {
      return $value === 1;
    }
    if (is_string($value) && in_array($value, ['0', '1', 'true', 'false'], TRUE)) {
      return $value === '1' || $value === 'true';
    }
    throw new \InvalidArgumentException(sprintf('%s must be a boolean.', $name));
  }

}
