<?php

declare(strict_types=1);

namespace Drupal\ancora_engine\Engine;

/**
 * Nothing of a run was published (JobOutputInterface::publish()).
 *
 * The reason is one of the constants. The message holds no secret: it is
 * stored in the job and shown to its users.
 */
final class PublicationException extends \RuntimeException {

  public const STALE = 'stale';
  public const INVALID = 'invalid';
  public const FENCED = 'fenced';
  public const FAILED = 'failed';

  public function __construct(
    public readonly string $reason,
    string $message,
    ?\Throwable $previous = NULL,
  ) {
    parent::__construct($message, 0, $previous);
  }

}
