<?php

declare(strict_types=1);

namespace Divengine\Codegen\Model;

/** A method of a service: a SQL statement with named parameters ($name). */
final readonly class ServiceMethod
{
    /**
     * @param list<string> $params
     */
    public function __construct(
        public string $name,
        public string $description,
        public array $params,
        public string $sql,
    ) {
    }
}
