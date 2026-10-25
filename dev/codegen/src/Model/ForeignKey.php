<?php

declare(strict_types=1);

namespace Divengine\Codegen\Model;

/** A foreign key of a table. */
final readonly class ForeignKey
{
    /**
     * @param list<ForeignKeyColumn> $columns
     */
    public function __construct(
        public array $columns,
        public string $foreignSchema,
        public string $foreignTable,
        public bool $onDeleteCascade,
        public bool $onUpdateCascade,
    ) {
    }
}
