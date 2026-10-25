<?php

declare(strict_types=1);

namespace Divengine\Codegen\Sql;

use Divengine\Codegen\Model\ServiceMethod;
use InvalidArgumentException;

/**
 * Parameterized SQL of a service method (#35).
 *
 * Each "$name" or "'$name'" reference of the method SQL becomes a $1, $2, ...
 * placeholder (one per distinct parameter, numbered in the order of the
 * method's "params"); the values are bound and never interpolated. A "$"
 * that cannot be bound (unknown name, unbalanced quote, a placeholder inside
 * a string literal, "{$...}") is refused.
 */
final class ServiceSql
{
    /**
     * @return array{sql: string, params: list<string>}
     */
    public static function parameterize(ServiceMethod $method): array
    {
        $index = [];
        foreach ($method->params as $i => $param) {
            $index[$param] = $i + 1;
        }
        $unknown = [];
        $sql = preg_replace_callback(
            "/'?\\$([A-Za-z_][A-Za-z0-9_]*)'?/",
            static function (array $m) use ($index, &$unknown): string {
                if (!isset($index[$m[1]])) {
                    $unknown[] = $m[1];
                    return $m[0];
                }
                // quoted on both sides or on none
                if (($m[0][0] === "'") !== (substr($m[0], -1) === "'")) {
                    $unknown[] = $m[1];
                    return $m[0];
                }

                return '$' . $index[$m[1]];
            },
            $method->sql,
        );
        if ($sql === null) {
            throw new InvalidArgumentException("service method {$method->name}: invalid SQL");
        }
        // a placeholder inside a string literal ('%$name%') would be plain text
        $inQuote = false;
        $length = strlen($sql);
        for ($i = 0; $i < $length; $i++) {
            if ($sql[$i] === "'") {
                $inQuote = !$inQuote;
            } elseif ($inQuote && $sql[$i] === '$' && $i + 1 < $length && ctype_digit($sql[$i + 1])) {
                $unknown[] = 'placeholder in a string literal';
            }
        }
        if ($unknown !== [] || str_contains($sql, '{$')) {
            throw new InvalidArgumentException(
                "service method {$method->name}: unsupported \$ reference in SQL (" . implode(', ', $unknown) . ')'
            );
        }

        return ['sql' => $sql, 'params' => $method->params];
    }
}
