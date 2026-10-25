<?php

declare(strict_types=1);

namespace Divengine\Codegen\Model;

/** A column of a table. */
final readonly class Field
{
    public function __construct(
        public string $name,
        public string $type,
        public int $long,
        public bool $allowNull,
        public bool $unique,
    ) {
    }
}
