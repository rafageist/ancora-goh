<?php

declare(strict_types=1);

namespace Drupal\ancora_engine\Engine;

/**
 * An engine database error, with its SQLSTATE and without secrets.
 */
final class EngineException extends \RuntimeException {

  public function __construct(
    string $message,
    public readonly ?string $sqlstate = NULL,
  ) {
    parent::__construct($message);
  }

}
