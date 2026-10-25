<?php

declare(strict_types=1);

namespace Divengine\Codegen\Model;

/** A database view. */
final readonly class View
{
    public function __construct(
        public string $schema,
        public string $name,
        public string $sql,
    ) {
    }
}
