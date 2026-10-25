<?php

declare(strict_types=1);

namespace Divengine\Codegen\Model;

/** A column pair of a foreign key: local column and referenced column. */
final readonly class ForeignKeyColumn
{
    public function __construct(
        public string $local,
        public string $foreign,
    ) {
    }
}
