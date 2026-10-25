<?php

declare(strict_types=1);

namespace Divengine\Codegen\Model;

/** A database function. */
final readonly class DbFunction
{
    /**
     * @param list<FunctionParam> $params
     */
    public function __construct(
        public string $schema,
        public string $name,
        public string $return,
        public array $params,
    ) {
    }
}
