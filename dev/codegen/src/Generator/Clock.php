<?php

declare(strict_types=1);

namespace Divengine\Codegen\Generator;

use InvalidArgumentException;

/**
 * The "@updated:" timestamp written in the generated doc comments
 * ("d/m/y h:i:s", as cogePHP wrote it).
 *
 * When SOURCE_DATE_EPOCH is set (reproducible builds), the timestamp is that
 * moment in UTC, so two runs write the same bytes. Otherwise it is the local
 * time at the start of the run.
 */
final class Clock
{
    public const FORMAT = 'd/m/y h:i:s';

    public static function updated(?string $sourceDateEpoch = null): string
    {
        $epoch = $sourceDateEpoch ?? (getenv('SOURCE_DATE_EPOCH') ?: null);
        if ($epoch === null || $epoch === '') {
            return date(self::FORMAT);
        }
        if (preg_match('/^[0-9]+$/', $epoch) !== 1) {
            throw new InvalidArgumentException('SOURCE_DATE_EPOCH must be a non-negative integer');
        }

        return gmdate(self::FORMAT, (int) $epoch);
    }
}
