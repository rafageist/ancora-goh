<?php

declare(strict_types=1);

namespace Drupal\ancora_engine\Engine;

/**
 * What happened to one generar() run in the engine database.
 */
final class RunResult {

  /**
   * Status: generar() returned normally; the transaction was committed.
   */
  public const RETURNED = 'returned';

  /**
   * Status: placements broke an engine invariant; rolled back.
   *
   * The function returned, but a collision or an overflow was found.
   */
  public const INVALID = 'invalid';

  /**
   * Status: the statement failed or was cancelled; rolled back.
   */
  public const ERROR = 'error';

  /**
   * Why the worker stopped the run itself, if it did.
   */
  public const STOP_HARD_CANCEL = 'hard_cancel';
  public const STOP_WALL_CLOCK = 'wall_clock';
  public const STOP_FENCED = 'fenced';

  /**
   * Constructs a run result.
   *
   * @param string $status
   *   One of the RETURNED, INVALID or ERROR constants.
   * @param array $metrics
   *   Keys demands, placed, unplaced, collisions, overflow, digest (when
   *   returned or invalid).
   * @param int $durationMs
   *   Duration of the generar() statement.
   * @param string|null $sqlstate
   *   SQLSTATE of the error, if any.
   * @param string|null $message
   *   Error summary, without connection details.
   * @param string|null $stoppedBy
   *   One of the STOP_* constants when the worker stopped the run.
   * @param bool $cancelRowLeft
   *   TRUE when a generador.cancelar row of the scope was still there after
   *   generar() returned (it was not consumed: the run had finished).
   * @param int $replaced
   *   Placements of the scope deleted before the run (replace semantics).
   * @param array $input
   *   What the job input service loaded before the run (synced jobs): the
   *   input snapshot hash and the row changes. Empty for other jobs, and
   *   when the run rolled back before the input was loaded.
   */
  public function __construct(
    public readonly string $status,
    public readonly array $metrics = [],
    public readonly int $durationMs = 0,
    public readonly ?string $sqlstate = NULL,
    public readonly ?string $message = NULL,
    public readonly ?string $stoppedBy = NULL,
    public readonly bool $cancelRowLeft = FALSE,
    public readonly int $replaced = 0,
    public readonly array $input = [],
  ) {}

  /**
   * Whether statement_timeout stopped the run.
   */
  public function isStatementTimeout(): bool {
    return $this->sqlstate === '57014' && $this->message !== NULL && str_contains($this->message, 'statement timeout');
  }

}
