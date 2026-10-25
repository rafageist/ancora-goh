<?php

declare(strict_types=1);

namespace Divengine\Codegen\Model;

/**
 * A validated data model: tables, and views and functions grouped by schema
 * in the order of their first appearance.
 */
final readonly class Model
{
    /**
     * @param list<Table> $tables
     * @param array<string, list<View>> $views
     * @param array<string, list<DbFunction>> $functions
     */
    public function __construct(
        public string $prefix,
        public array $tables,
        public array $views,
        public array $functions,
    ) {
    }
}
