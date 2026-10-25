<?php

declare(strict_types=1);

namespace Divengine\Codegen\Model;

/** A service model (dev/services/*.json): a class of named SQL queries. */
final readonly class Service
{
    /**
     * @param list<ServiceMethod> $methods
     */
    public function __construct(
        public string $name,
        public string $description,
        public array $methods,
    ) {
    }
}
