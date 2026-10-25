<?php

declare(strict_types=1);

namespace Divengine\Codegen\Sql;

use Divengine\Codegen\Profile\Profile;

/**
 * Model types and the bound-parameter kinds of PartModel::sqlValue()
 * (issue #35): "bool" for booleans, "number" for the types that were written
 * without quotes, "text" for everything else.
 */
final class SqlTypes
{
    public function __construct(private readonly Profile $profile)
    {
    }

    /** The type was written between quotes (unknown types are). */
    public function isQuoted(string $type): bool
    {
        $quoted = $this->profile->list('types.quoted');

        return array_key_exists($type, $quoted) ? (bool) $quoted[$type] : true;
    }

    /** Kind of bound parameter of a model type: "bool", "number" or "text". */
    public function kind(string $type): string
    {
        if ($type === 'bool' || $type === 'boolean') {
            return 'bool';
        }

        return $this->isQuoted($type) ? 'text' : 'number';
    }

    /** SQL type of a model type in the creator script. */
    public function sqlType(string $type): string
    {
        $map = $this->profile->list('types.database');

        return array_key_exists($type, $map) ? (string) $map[$type] : $type;
    }
}
