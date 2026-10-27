<?php

declare(strict_types=1);

namespace Ancora\Portal\Log;

use Psr\Log\AbstractLogger;
use Stringable;

/**
 * PSR-3 logger writing one JSON line per record to a stream (stderr by
 * default, which PHP-FPM and Docker collect). Context values are scalars
 * only; callers pass masked identifiers, never tokens, secrets or full
 * email addresses (see mask()). Under PHP-FPM, where worker stderr is
 * discarded, pass $useErrorLog to write through error_log() into the pool's
 * own log file.
 */
final class StderrLogger extends AbstractLogger
{
    /** @var resource */
    private $stream;

    /** @param resource|null $stream */
    public function __construct($stream = null, private readonly bool $useErrorLog = false)
    {
        $this->stream = $stream ?? fopen('php://stderr', 'w');
    }

    /** @param array<string, mixed> $context */
    public function log($level, string|Stringable $message, array $context = []): void
    {
        $clean = [];
        foreach ($context as $k => $v) {
            $clean[(string) $k] = is_scalar($v) || $v === null ? $v : get_debug_type($v);
        }
        $line = json_encode([
            'ts' => gmdate('Y-m-d\TH:i:s\Z'),
            'level' => (string) $level,
            'msg' => (string) $message,
            'ctx' => $clean,
        ], JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE);
        if ($this->useErrorLog) {
            error_log((string) $line);
            return;
        }
        fwrite($this->stream, $line . "\n");
    }

    /** "a***@example.org" */
    public static function mask(string $email): string
    {
        $at = strrpos($email, '@');
        if ($at === false || $at === 0) {
            return '***';
        }
        return substr($email, 0, 1) . '***' . substr($email, $at);
    }
}
