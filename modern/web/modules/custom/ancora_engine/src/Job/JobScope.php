<?php

declare(strict_types=1);

namespace Drupal\ancora_engine\Job;

/**
 * The specialty, level and period of a generation, as engine database ids.
 */
final class JobScope {

  /**
   * Maximum length of an engine id (the job table columns).
   */
  public const MAX_LENGTH = 255;

  public function __construct(
    public readonly string $specialty,
    public readonly string $level,
    public readonly string $period,
  ) {
    foreach (['specialty' => $specialty, 'level' => $level, 'period' => $period] as $name => $value) {
      if ($value === '' || trim($value) !== $value) {
        throw new \InvalidArgumentException(sprintf('The %s id must be a non-empty id without surrounding spaces.', $name));
      }
      if (mb_strlen($value) > self::MAX_LENGTH || preg_match('/[\x00-\x1F\x7F]/u', $value) || !mb_check_encoding($value, 'UTF-8')) {
        throw new \InvalidArgumentException(sprintf('The %s id is too long or contains control characters.', $name));
      }
    }
  }

  /**
   * The scope as generar() parameters 1 to 3.
   *
   * @return string[]
   *   Specialty, level and period.
   */
  public function toParameters(): array {
    return [$this->specialty, $this->level, $this->period];
  }

  /**
   * A short label, for messages.
   */
  public function label(): string {
    return $this->specialty . ' / ' . $this->level . ' / ' . $this->period;
  }

}
